<?php

use App\Jobs\GenerateCatalogProductEmbedding;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use App\Services\Catalog\CatalogSearchableTextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

uses(RefreshDatabase::class);

function phase6EmbeddingFake(): void
{
    Embeddings::fake(function (EmbeddingsPrompt $prompt) {
        return array_map(
            fn () => Embeddings::fakeEmbedding(1536),
            $prompt->inputs
        );
    });
}

// ─── Phase 6.2 — Embedding Status Tracking ────────────────────────────────────

it('sets embedded_at after successful embedding generation', function () {
    phase6EmbeddingFake();

    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Fase 6',
        'brand_name' => 'Marca Fase 6',
        'searchable_text' => null,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    $before = now()->subSecond();

    (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService);

    $product->refresh();

    expect($product->embedded_at)->not->toBeNull()
        ->and($product->embedded_at->gte($before))->toBeTrue();
});

it('scopeWithoutEmbedding returns only records with null embedding', function () {
    $tenant = Tenant::factory()->create();

    CatalogProduct::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    CatalogProduct::factory()->embedded()->create([
        'tenant_id' => $tenant->id,
    ]);

    $results = CatalogProduct::withoutEmbedding()->where('tenant_id', $tenant->id)->get();

    expect($results)->toHaveCount(2);
    $results->each(fn ($p) => expect($p->embedding)->toBeNull());
});

it('scopeStale returns records where embedded_at is null', function () {
    $tenant = Tenant::factory()->create();

    $noEmbedding = CatalogProduct::factory()->create([
        'tenant_id' => $tenant->id,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    $results = CatalogProduct::stale()->where('tenant_id', $tenant->id)->pluck('id');

    expect($results)->toContain($noEmbedding->id);
});

it('scopeStale returns records where embedded_at is older than updated_at', function () {
    $tenant = Tenant::factory()->create();

    $stale = CatalogProduct::factory()->create(['tenant_id' => $tenant->id]);

    DB::table('catalog_products')->where('id', $stale->id)->update([
        'embedded_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now(),
    ]);

    $results = CatalogProduct::stale()->where('tenant_id', $tenant->id)->pluck('id');

    expect($results)->toContain($stale->id);
});

it('scopeStale does not return records that are already current', function () {
    $tenant = Tenant::factory()->create();

    $current = CatalogProduct::factory()->create(['tenant_id' => $tenant->id]);

    DB::table('catalog_products')->where('id', $current->id)->update([
        'embedded_at' => Carbon::now()->addMinute(),
        'updated_at' => Carbon::now()->subMinute(),
    ]);

    $results = CatalogProduct::stale()->where('tenant_id', $tenant->id)->pluck('id');

    expect($results)->not->toContain($current->id);
});

it('logs embedding failures without crashing the job (log assertion)', function () {
    Embeddings::fake(fn () => throw new RuntimeException('AI provider unavailable'));

    Log::shouldReceive('warning')->zeroOrMoreTimes()->andReturnNull();
    Log::shouldReceive('error')
        ->once()
        ->with('GenerateCatalogProductEmbedding: failed to generate embedding', Mockery::any())
        ->andReturnNull();

    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Erro Fase 6',
        'brand_name' => 'Marca Teste',
        'searchable_text' => 'texto pesquisavel',
        'embedding' => null,
        'embedded_at' => null,
    ]);

    expect(fn () => (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService))
        ->toThrow(RuntimeException::class, 'AI provider unavailable');

    $product->refresh();

    expect($product->embedded_at)->toBeNull()
        ->and($product->embedding)->toBeNull();
});
