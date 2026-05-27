<?php

use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use App\Services\Catalog\CatalogSearchService;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

// ============================================================
// Phase 4.1 — CatalogSearchService
// ============================================================

function phase4EmbeddingFake(): void
{
    Embeddings::fake(function (EmbeddingsPrompt $prompt) {
        return array_map(
            fn () => Embeddings::fakeEmbedding(3072),
            $prompt->inputs
        );
    });
}

it('throws InvalidArgumentException for an empty query string', function () {
    $service = new CatalogSearchService;

    expect(fn () => $service->search('', 1))
        ->toThrow(InvalidArgumentException::class);
});

it('returns CatalogProduct records ordered by semantic similarity (mock neighbor search)', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    CatalogProduct::factory()->embedded()->count(3)->create(['tenant_id' => $tenant->id]);

    $results = (new CatalogSearchService)->search('produto de teste', $tenant->id);

    expect($results->count())->toBe(3)
        ->and($results->first())->toBeInstanceOf(CatalogProduct::class);
});

it('limits results to the configured limit', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    CatalogProduct::factory()->embedded()->count(10)->create(['tenant_id' => $tenant->id]);

    $results = (new CatalogSearchService)->search('produto', $tenant->id, 3);

    expect($results->count())->toBe(3);
});

it('eager-loads packages on search results', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    $product = CatalogProduct::factory()->embedded()->create(['tenant_id' => $tenant->id]);
    CatalogPackage::factory()->count(2)->create(['catalog_product_id' => $product->id]);

    $results = (new CatalogSearchService)->search('produto', $tenant->id);

    expect($results->first()->relationLoaded('packages'))->toBeTrue()
        ->and($results->first()->packages)->toHaveCount(2);
});

it('returns an empty collection when no results match', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();

    $results = (new CatalogSearchService)->search('produto', $tenant->id);

    expect($results)->toBeEmpty();
});

it('searchAsRagContext returns the expected array structure', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    $product = CatalogProduct::factory()->embedded()->create([
        'tenant_id' => $tenant->id,
        'product_name' => 'Arroz Premium',
        'product_description' => 'Arroz branco tipo 1',
        'brand_name' => 'Marca Teste',
        'category_name' => 'Alimentos',
        'sub_category_name' => 'Cereais',
        'line_name' => 'Premium',
    ]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package' => 'PKG-001',
        'sku_package_name' => 'Caixa 12 un',
        'ean' => '7891234567890',
    ]);

    $context = (new CatalogSearchService)->searchAsRagContext('arroz', $tenant->id);

    expect($context)->toBeArray()
        ->and($context)->not->toBeEmpty()
        ->and($context[0])->toHaveKeys([
            'codigo_padrao', 'product_name', 'product_description', 'brand_name',
            'category_name', 'sub_category_name', 'line_name', 'product_img_url', 'packages',
        ])
        ->and($context[0]['product_name'])->toBe('Arroz Premium')
        ->and($context[0]['packages'])->toBeArray();
});

it('searchAsRagContext never includes embedding values in output', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    CatalogProduct::factory()->embedded()->create(['tenant_id' => $tenant->id]);

    $context = (new CatalogSearchService)->searchAsRagContext('produto', $tenant->id);

    expect($context[0])->not->toHaveKey('embedding')
        ->and($context[0])->not->toHaveKey('searchable_text')
        ->and($context[0])->not->toHaveKey('embedded_at');
});

it('searchAsRagContext returns an empty array when no results are found', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();

    $context = (new CatalogSearchService)->searchAsRagContext('produto', $tenant->id);

    expect($context)->toBeArray()
        ->and($context)->toBeEmpty();
});

it('searchAsRagContext includes all package fields for each product', function () {
    phase4EmbeddingFake();

    $tenant = Tenant::factory()->create();
    $product = CatalogProduct::factory()->embedded()->create(['tenant_id' => $tenant->id]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package' => 'PKG-TEST',
        'sku_package_name' => 'Embalagem Teste',
        'package_description' => 'Descrição da embalagem',
        'gross_weight' => '1.5000',
        'net_weight' => '1.2000',
        'ean' => '7891234567890',
        'package_img_url' => 'https://example.com/img.jpg',
    ]);

    $context = (new CatalogSearchService)->searchAsRagContext('produto', $tenant->id);

    expect($context[0]['packages'][0])->toHaveKeys([
        'sku_package', 'sku_package_name', 'package_description',
        'gross_weight', 'net_weight', 'ean', 'package_img_url',
    ]);
});
