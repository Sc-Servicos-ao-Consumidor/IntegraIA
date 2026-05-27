<?php

use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogSearchableTextService;

// ============================================================
// Phase 3.1 — CatalogSearchableTextService
// ============================================================

it('includes product_name, product_description, brand_name, category_name, sub_category_name, and line_name when present', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Arroz Tio João',
        'product_description' => 'Arroz branco tipo 1',
        'brand_name' => 'Tio João',
        'category_name' => 'Alimentos',
        'sub_category_name' => 'Cereais',
        'line_name' => 'Premium',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toContain('Arroz Tio João')
        ->and($text)->toContain('Arroz branco tipo 1')
        ->and($text)->toContain('Tio João')
        ->and($text)->toContain('Alimentos')
        ->and($text)->toContain('Cereais')
        ->and($text)->toContain('Premium');
});

it('omits null fields from the output', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Teste',
        'product_description' => null,
        'brand_name' => null,
        'category_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Produto Teste');
});

it('omits empty-string fields from the output', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Teste',
        'product_description' => '',
        'brand_name' => '',
        'category_name' => 'Alimentos',
        'sub_category_name' => '',
        'line_name' => '',
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Produto Teste Alimentos');
});

it('appends sku_package_name, package_description, and ean from all related packages', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Produto Base',
        'product_description' => null,
        'brand_name' => null,
        'category_name' => null,
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

    expect($text)->toContain('Caixa 12 unidades')
        ->and($text)->toContain('Embalagem com 12 unidades')
        ->and($text)->toContain('7891234567890')
        ->and($text)->toContain('Fardo 24 unidades')
        ->and($text)->toContain('7891234567891');
});

it('returns an empty string when all fields are null and no packages exist', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => '',
        'product_description' => null,
        'brand_name' => null,
        'category_name' => null,
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
        'product_description' => null,
        'brand_name' => null,
        'category_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $text = $service->build($product);

    expect($text)->toBe('Único Campo');
});

it('buildAndStore persists searchable_text on the CatalogProduct record', function () {
    $product = CatalogProduct::factory()->create([
        'product_name' => 'Persistido',
        'brand_name' => 'Marca X',
        'product_description' => null,
        'category_name' => null,
        'sub_category_name' => null,
        'line_name' => null,
        'searchable_text' => null,
    ]);

    $service = new CatalogSearchableTextService;
    $service->buildAndStore($product);

    expect($product->fresh()->searchable_text)->toBe('Persistido Marca X');
});
