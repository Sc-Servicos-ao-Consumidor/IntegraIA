<?php

use App\Jobs\Catalog\CatalogSearchJob;
use App\Jobs\Catalog\WhatsAppTypingJob;
use App\Models\CatalogRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

// ============================================================
// Phase 5.9 — CatalogPipelineController
// ============================================================

function phase5CreateUserWithTenant(): array
{
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $user->tenants()->attach($tenant->id);

    return [$user, $tenant];
}

it('POST /api/catalog/pipeline returns 202 Accepted with a request_id when input is valid', function () {
    Bus::fake();

    [$user] = phase5CreateUserWithTenant();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/catalog/pipeline', [
        'question' => 'Qual é o melhor arroz?',
        'contact_id' => '+5511999999999',
    ]);

    $response->assertStatus(202)
        ->assertJsonStructure(['request_id'])
        ->assertJsonPath('request_id', fn ($id) => is_int($id));
});

it('POST /api/catalog/pipeline creates a CatalogRequest and dispatches the pipeline jobs', function () {
    Bus::fake();

    [$user] = phase5CreateUserWithTenant();

    $this->actingAs($user, 'sanctum')->postJson('/api/catalog/pipeline', [
        'question' => 'Quais produtos têm desconto?',
        'contact_id' => '+5511888888888',
    ]);

    expect(CatalogRequest::count())->toBe(1);
    Bus::assertDispatched(WhatsAppTypingJob::class);
    Bus::assertDispatched(CatalogSearchJob::class);
});

it('POST /api/catalog/pipeline returns 422 when question is missing', function () {
    [$user] = phase5CreateUserWithTenant();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/catalog/pipeline', [
        'contact_id' => '+5511999999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['question']);
});

it('POST /api/catalog/pipeline returns 422 when question is empty', function () {
    [$user] = phase5CreateUserWithTenant();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/catalog/pipeline', [
        'question' => '',
        'contact_id' => '+5511999999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['question']);
});

it('POST /api/catalog/pipeline returns 422 when contact_id is missing', function () {
    [$user] = phase5CreateUserWithTenant();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/catalog/pipeline', [
        'question' => 'Qual produto?',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['contact_id']);
});

it('POST /api/catalog/pipeline returns 401 for unauthenticated requests', function () {
    $response = $this->postJson('/api/catalog/pipeline', [
        'question' => 'Qual produto?',
        'contact_id' => '+5511999999999',
    ]);

    $response->assertStatus(401);
});
