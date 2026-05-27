<?php

use App\Jobs\GenerateCatalogProductEmbedding;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogSearchableTextService;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

// ============================================================
// Phase 3.2 — GenerateCatalogProductEmbedding Job
// ============================================================

function phase3EmbeddingFake(): void
{
    Embeddings::fake(function (EmbeddingsPrompt $prompt) {
        return array_map(
            fn () => Embeddings::fakeEmbedding(3072),
            $prompt->inputs
        );
    });
}

it('generates an embedding and sets embedded_at on the CatalogProduct', function () {
    phase3EmbeddingFake();

    $product = CatalogProduct::factory()->create([
        'product_name' => 'Arroz Premium',
        'brand_name' => 'Marca Test',
        'searchable_text' => null,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService);

    $product->refresh();

    expect($product->embedding)->not->toBeNull()
        ->and($product->embedded_at)->not->toBeNull();
});

it('refreshes searchable_text before generating the embedding', function () {
    phase3EmbeddingFake();

    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Atualizado',
        'brand_name' => 'Marca Nova',
        'product_description' => null,
        'category_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
        'searchable_text' => 'texto antigo',
        'embedding' => null,
        'embedded_at' => null,
    ]);

    (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService);

    $product->refresh();

    expect($product->searchable_text)->toContain('Produto Atualizado')
        ->and($product->searchable_text)->toContain('Marca Nova')
        ->and($product->searchable_text)->not->toBe('texto antigo');
});

it('skips embedding and logs a warning when searchable_text is empty', function () {
    phase3EmbeddingFake();

    Log::shouldReceive('warning')
        ->once()
        ->with('GenerateCatalogProductEmbedding: skipping empty searchable_text', Mockery::any());

    $product = CatalogProduct::factory()->create([
        'product_name' => '',
        'product_description' => null,
        'brand_name' => null,
        'category_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
        'searchable_text' => null,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService);

    $product->refresh();

    expect($product->embedding)->toBeNull()
        ->and($product->embedded_at)->toBeNull();

    Embeddings::assertNothingGenerated();
});

it('returns early without error when the CatalogProduct does not exist', function () {
    phase3EmbeddingFake();

    $nonExistentId = 999999;

    expect(fn () => (new GenerateCatalogProductEmbedding($nonExistentId))->handle(new CatalogSearchableTextService))
        ->not->toThrow(Throwable::class);

    Embeddings::assertNothingGenerated();
});

it('marks the job as failed after exhausting retries (mock AI failure)', function () {
    Embeddings::fake(function () {
        throw new RuntimeException('AI provider unavailable');
    });

    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Erro',
        'embedding' => null,
        'embedded_at' => null,
    ]);

    expect(fn () => (new GenerateCatalogProductEmbedding($product->id))->handle(new CatalogSearchableTextService))
        ->toThrow(RuntimeException::class, 'AI provider unavailable');

    $product->refresh();

    expect($product->embedding)->toBeNull()
        ->and($product->embedded_at)->toBeNull();
});
