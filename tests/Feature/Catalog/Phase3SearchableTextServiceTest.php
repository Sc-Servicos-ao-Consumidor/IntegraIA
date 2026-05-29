<?php

use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogSearchableTextService;

// ============================================================
// Phase 3.1 — CatalogSearchableTextService
// ============================================================

it('includes product_name, brand_name, sub_category_name, and line_name with labels', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Arroz Tio João',
        'brand_name' => 'Tio João',
        'sub_category_name' => 'Cereais',
        'line_name' => 'Premium',
        'product_description' => 'Descrição ignorada',
        'category_name' => 'Alimentos',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toContain('Produto: Arroz Tio João')
        ->and($text)->toContain('Marca: Tio João')
        ->and($text)->toContain('Subcategoria: Cereais')
        ->and($text)->toContain('Linha: Premium')
        ->and($text)->not->toContain('Descrição ignorada')
        ->and($text)->not->toContain('Alimentos');
});

it('omits null fields from the output', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Teste',
        'brand_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Produto: Produto Teste');
});

it('omits empty-string fields from the output', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Teste',
        'brand_name' => '',
        'sub_category_name' => '',
        'line_name' => '',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Produto: Produto Teste');
});

it('appends only sku_package_name from packages under an "Embalagens disponíveis" label', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Base',
        'brand_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package_name' => 'Caixa 12 unidades',
        'package_description' => 'Embalagem com 12 unidades',
        'ean' => '7891234567890',
    ]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package_name' => 'Fardo 24 unidades',
        'package_description' => null,
        'ean' => '7891234567891',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toContain('Embalagens disponíveis: Caixa 12 unidades; Fardo 24 unidades')
        ->and($text)->not->toContain('7891234567890')
        ->and($text)->not->toContain('Embalagem com 12 unidades');
});

it('returns an empty string when all fields are null and no packages exist', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => '',
        'brand_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('');
});

it('returns a non-empty string even when only product_name is present', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Único Campo',
        'brand_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Produto: Único Campo');
});

it('buildAndStore persists the labeled searchable_text on the CatalogProduct record', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Persistido',
        'brand_name' => 'Marca X',
        'sub_category_name' => null,
        'line_name' => null,
        'searchable_text' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $service->buildAndStore($product);

    expect($product->fresh()->searchable_text)->toBe("Produto: Persistido\nMarca: Marca X");
});

it('deduplicates identical sku_package_name values across packages', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Duplicado',
        'brand_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package_name' => 'Caixa 12 unidades',
    ]);

    CatalogPackage::factory()->create([
        'catalog_product_id' => $product->id,
        'sku_package_name' => 'Caixa 12 unidades',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect(substr_count($text, 'Caixa 12 unidades'))->toBe(1);
});
