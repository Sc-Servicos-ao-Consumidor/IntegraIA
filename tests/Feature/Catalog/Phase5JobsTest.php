<?php

use App\Ai\Agents\CatalogAnswerAgent;
use App\Integrations\Botmaker\BotmakerService;
use App\Integrations\Vendas\VendasClient;
use App\Integrations\Vendas\VendasProductService;
use App\Jobs\Catalog\CatalogSearchJob;
use App\Jobs\Catalog\RunCatalogAgentJob;
use App\Jobs\WhatsApp\SendWhatsAppMessageJob;
use App\Jobs\WhatsApp\WhatsAppTypingJob;
use App\Models\CatalogRequest;
use App\Models\Tenant;
use App\Services\Catalog\CatalogSearchService;

// ============================================================
// RunCatalogAgentJob
// ============================================================

function makeVendasService(): VendasProductService
{
    return new VendasProductService(new VendasClient);
}

it('RunCatalogAgentJob transitions status to processing and saves the agent answer', function () {
    CatalogAnswerAgent::fake(['Recomendo o Azeite Extravirgem Nova Oliva.']);

    $request = CatalogRequest::factory()->create(['question' => 'Qual azeite vocês têm?']);

    (new RunCatalogAgentJob($request->id))->handle(makeVendasService());

    $request->refresh();
    expect($request->status->name)->toBe('processing')
        ->and($request->ai_answer)->toBe('Recomendo o Azeite Extravirgem Nova Oliva.')
        ->and($request->started_at)->not->toBeNull();
});

it('RunCatalogAgentJob transitions status to failed and sets completed_at on exception', function () {
    CatalogAnswerAgent::fake(fn () => throw new RuntimeException('Agent failed'));

    $request = CatalogRequest::factory()->create();

    expect(fn () => (new RunCatalogAgentJob($request->id))->handle(makeVendasService()))
        ->toThrow(RuntimeException::class);

    $request->refresh();
    expect($request->status->name)->toBe('failed')
        ->and($request->completed_at)->not->toBeNull();
});

it('RunCatalogAgentJob returns early without error when CatalogRequest does not exist', function () {
    CatalogAnswerAgent::fake();

    expect(fn () => (new RunCatalogAgentJob(99999))->handle(makeVendasService()))
        ->not->toThrow(Throwable::class);

    CatalogAnswerAgent::assertNeverPrompted();
});

it('RunCatalogAgentJob stores the session_id on the CatalogRequest', function () {
    CatalogAnswerAgent::fake(['Resposta do agente.']);

    $request = CatalogRequest::factory()->create(['session_id' => 'SESSION123']);

    (new RunCatalogAgentJob($request->id))->handle(makeVendasService());

    expect($request->fresh()->session_id)->toBe('SESSION123');
});

it('CatalogAnswerAgent messages() returns empty array when no previous session history exists', function () {
    $request = CatalogRequest::factory()->create(['session_id' => 'NEW_SESSION']);

    $agent = new CatalogAnswerAgent(
        vendasProductService: makeVendasService(),
        tenantId: $request->tenant_id,
        sessionId: $request->session_id,
        catalogRequestId: $request->id,
    );

    expect($agent->messages())->toBeEmpty();
});

it('CatalogAnswerAgent messages() loads previous answered requests as history', function () {
    $sessionId = 'HIST_SESSION_'.fake()->bothify('????');

    $previous = CatalogRequest::factory()->create([
        'session_id' => $sessionId,
        'question' => 'Qual o preço do azeite?',
        'ai_answer' => 'O azeite custa R$ 29,90.',
    ]);

    $current = CatalogRequest::factory()->create([
        'session_id' => $sessionId,
        'question' => 'E o arroz?',
    ]);

    $agent = new CatalogAnswerAgent(
        vendasProductService: makeVendasService(),
        tenantId: $current->tenant_id,
        sessionId: $sessionId,
        catalogRequestId: $current->id,
    );

    $messages = collect($agent->messages());

    expect($messages)->toHaveCount(2)
        ->and($messages[0]->role->value)->toBe('user')
        ->and($messages[0]->content)->toBe('Qual o preço do azeite?')
        ->and($messages[1]->role->value)->toBe('assistant')
        ->and($messages[1]->content)->toBe('O azeite custa R$ 29,90.');
});

it('CatalogAnswerAgent messages() does not include the current request in history', function () {
    $sessionId = 'NOSELF_SESSION_'.fake()->bothify('????');

    $current = CatalogRequest::factory()->create([
        'session_id' => $sessionId,
        'question' => 'Qual arroz?',
        'ai_answer' => 'Temos Tio João.',
    ]);

    $agent = new CatalogAnswerAgent(
        vendasProductService: makeVendasService(),
        tenantId: $current->tenant_id,
        sessionId: $sessionId,
        catalogRequestId: $current->id,
    );

    expect($agent->messages())->toBeEmpty();
});

it('CatalogAnswerAgent messages() limits history to 7 messages', function () {
    $sessionId = 'LIMIT_SESSION_'.fake()->bothify('????');

    $tenant = Tenant::factory()->create();

    for ($i = 1; $i <= 5; $i++) {
        CatalogRequest::factory()->create([
            'tenant_id' => $tenant->id,
            'session_id' => $sessionId,
            'question' => "Pergunta {$i}",
            'ai_answer' => "Resposta {$i}",
            'created_at' => now()->subMinutes(10 - $i),
        ]);
    }

    $current = CatalogRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'session_id' => $sessionId,
        'question' => 'Pergunta atual',
    ]);

    $agent = new CatalogAnswerAgent(
        vendasProductService: makeVendasService(),
        tenantId: $current->tenant_id,
        sessionId: $sessionId,
        catalogRequestId: $current->id,
    );

    expect(collect($agent->messages()))->toHaveCount(7);
});

// ============================================================
// Phase 5.4 — WhatsAppTypingJob
// ============================================================

it('WhatsAppTypingJob can be instantiated with contactId', function () {
    $job = new WhatsAppTypingJob('+5511999999999');

    expect($job->contactId)->toBe('+5511999999999');
});

it('WhatsAppTypingJob does not throw during handle() execution', function () {
    $job = new WhatsAppTypingJob('+5511999999999');
    $botmakerService = Mockery::mock(BotmakerService::class);
    $botmakerService->shouldReceive('sendTyping')->once();

    expect(fn () => $job->handle($botmakerService))->not->toThrow(Throwable::class);
});

// ============================================================
// Phase 5.5 — CatalogSearchJob
// ============================================================

it('CatalogSearchJob loads the CatalogRequest and runs the search', function () {
    $request = CatalogRequest::factory()->create(['question' => 'Qual arroz?']);

    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldReceive('searchAsRagContext')
        ->once()
        ->with('Qual arroz?', $request->tenant_id)
        ->andReturn([['product_name' => 'Arroz Premium']]);

    $job = new CatalogSearchJob($request->id);
    $job->handle($mockService);

    $request->refresh();
    expect($request->search_results)->toBeArray()
        ->and($request->search_results[0]['product_name'])->toBe('Arroz Premium');
});

it('CatalogSearchJob saves the search_results array on the CatalogRequest', function () {
    $request = CatalogRequest::factory()->create();

    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldReceive('searchAsRagContext')
        ->once()
        ->andReturn([['product_name' => 'Produto X'], ['product_name' => 'Produto Y']]);

    $job = new CatalogSearchJob($request->id);
    $job->handle($mockService);

    $request->refresh();
    expect($request->search_results)->toHaveCount(2);
});

it('CatalogSearchJob sets started_at when the job runs', function () {
    $request = CatalogRequest::factory()->create(['started_at' => null]);

    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldReceive('searchAsRagContext')->once()->andReturn([]);

    $job = new CatalogSearchJob($request->id);
    $job->handle($mockService);

    $request->refresh();
    expect($request->started_at)->not->toBeNull();
});

it('CatalogSearchJob transitions status to processing at the start', function () {
    $request = CatalogRequest::factory()->create();

    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldReceive('searchAsRagContext')->once()->andReturn([]);

    $job = new CatalogSearchJob($request->id);
    $job->handle($mockService);

    $request->refresh();
    expect($request->status->name)->toBe('processing');
});

it('CatalogSearchJob transitions status to failed and sets completed_at on exception', function () {
    $request = CatalogRequest::factory()->create();

    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldReceive('searchAsRagContext')->once()->andThrow(new RuntimeException('Search failed'));

    $job = new CatalogSearchJob($request->id);

    expect(fn () => $job->handle($mockService))->toThrow(RuntimeException::class);

    $request->refresh();
    expect($request->status->name)->toBe('failed')
        ->and($request->completed_at)->not->toBeNull();
});

it('CatalogSearchJob returns early without error when CatalogRequest does not exist', function () {
    $mockService = Mockery::mock(CatalogSearchService::class);
    $mockService->shouldNotReceive('searchAsRagContext');

    $job = new CatalogSearchJob(99999);

    expect(fn () => $job->handle($mockService))->not->toThrow(Throwable::class);
});

// ============================================================
// Phase 5.7 — SendWhatsAppMessageJob
// ============================================================

it('SendWhatsAppMessageJob can be instantiated with a catalogRequestId', function () {
    $job = new SendWhatsAppMessageJob(42);

    expect($job->catalogRequestId)->toBe(42);
});

it('SendWhatsAppMessageJob transitions status to completed and sets completed_at after handle()', function () {
    $request = CatalogRequest::factory()->create([
        'ai_answer' => ['answer' => 'Resposta final', 'products' => []],
    ]);

    $job = new SendWhatsAppMessageJob($request->id);
    $job->handle();

    $request->refresh();
    expect($request->status->name)->toBe('completed')
        ->and($request->completed_at)->not->toBeNull();
});

it('SendWhatsAppMessageJob returns early without error when CatalogRequest does not exist', function () {
    $job = new SendWhatsAppMessageJob(99999);

    expect(fn () => $job->handle())->not->toThrow(Throwable::class);
});
