<?php

use App\Jobs\Catalog\CatalogSearchJob;
use App\Jobs\Catalog\GenerateCatalogAnswerJob;
use App\Jobs\Catalog\SendWhatsAppMessageJob;
use App\Jobs\Catalog\WhatsAppTypingJob;
use App\Models\CatalogRequest;
use App\Models\Tenant;
use App\Services\Catalog\CatalogPipelineService;
use Illuminate\Support\Facades\Bus;

// ============================================================
// Phase 5.8 — CatalogPipelineService
// ============================================================

it('CatalogPipelineService creates a CatalogRequest record with pending status', function () {
    Bus::fake();

    $tenant = Tenant::factory()->create();
    $service = new CatalogPipelineService;

    $request = $service->dispatch('Quero comprar arroz', '+5511999999999', $tenant->id);

    expect($request)->toBeInstanceOf(CatalogRequest::class)
        ->and($request->status->name)->toBe('pending')
        ->and($request->question)->toBe('Quero comprar arroz')
        ->and($request->contact_id)->toBe('+5511999999999')
        ->and($request->tenant_id)->toBe($tenant->id);
});

it('CatalogPipelineService dispatches WhatsAppTypingJob as a standalone fire-and-forget job', function () {
    Bus::fake();

    $tenant = Tenant::factory()->create();
    $service = new CatalogPipelineService;

    $service->dispatch('Quero comprar arroz', '+5511999999999', $tenant->id);

    Bus::assertDispatched(WhatsAppTypingJob::class, fn (WhatsAppTypingJob $job) => $job->contactId === '+5511999999999' && $job->tenantId === $tenant->id);
});

it('CatalogPipelineService dispatches CatalogSearchJob, GenerateCatalogAnswerJob, and SendWhatsAppMessageJob as a chain', function () {
    Bus::fake();

    $tenant = Tenant::factory()->create();
    $service = new CatalogPipelineService;

    $catalogRequest = $service->dispatch('Quero comprar arroz', '+5511999999999', $tenant->id);

    Bus::assertChained([
        new CatalogSearchJob($catalogRequest->id),
        new GenerateCatalogAnswerJob($catalogRequest->id),
        new SendWhatsAppMessageJob($catalogRequest->id),
    ]);
});

it('CatalogPipelineService returns the created CatalogRequest', function () {
    Bus::fake();

    $tenant = Tenant::factory()->create();
    $service = new CatalogPipelineService;

    $result = $service->dispatch('Qual o melhor produto?', '+5511888888888', $tenant->id);

    expect($result)->toBeInstanceOf(CatalogRequest::class)
        ->and(CatalogRequest::find($result->id))->not->toBeNull();
});

it('CatalogPipelineService throws InvalidArgumentException when question is empty', function () {
    Bus::fake();

    $tenant = Tenant::factory()->create();
    $service = new CatalogPipelineService;

    expect(fn () => $service->dispatch('', '+5511999999999', $tenant->id))
        ->toThrow(InvalidArgumentException::class);
});
