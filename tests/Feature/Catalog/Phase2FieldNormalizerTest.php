<?php

use App\Services\Catalog\CatalogFieldNormalizer;

beforeEach(function () {
    $this->normalizer = new CatalogFieldNormalizer;
});

it('trims leading and trailing whitespace from string fields', function () {
    $result = $this->normalizer->normalize([
        'product_name' => '  Arroz Tio João  ',
        'brand_name' => "\t  Marca  \t",
    ]);

    expect($result['product_name'])->toBe('Arroz Tio João')
        ->and($result['brand_name'])->toBe('Marca');
});

it('converts empty strings to null', function () {
    $result = $this->normalizer->normalize([
        'product_name' => '',
        'category_name' => '   ',
        'sku_package' => '',
    ]);

    expect($result['product_name'])->toBeNull()
        ->and($result['category_name'])->toBeNull()
        ->and($result['sku_package'])->toBeNull();
});

it('preserves newlines in product_description and package_description', function () {
    $description = "Linha 1\nLinha 2\nLinha 3";

    $result = $this->normalizer->normalize([
        'product_description' => $description,
        'package_description' => $description,
    ]);

    expect($result['product_description'])->toBe($description)
        ->and($result['package_description'])->toBe($description);
});

it('casts gross_weight and net_weight to float values', function () {
    $result = $this->normalizer->normalize([
        'gross_weight' => '1.5',
        'net_weight' => '1,25',
    ]);

    expect($result['gross_weight'])->toBe(1.5)
        ->and($result['net_weight'])->toBe(1.25);
});

it('returns null for empty gross_weight and net_weight', function () {
    $result = $this->normalizer->normalize([
        'gross_weight' => '',
        'net_weight' => null,
    ]);

    expect($result['gross_weight'])->toBeNull()
        ->and($result['net_weight'])->toBeNull();
});

it('handles a fully populated row correctly', function () {
    $result = $this->normalizer->normalize([
        'product_id' => '  PROD-001  ',
        'product_name' => '  Arroz Tio João  ',
        'product_description' => "Descrição completa\nCom quebra de linha",
        'product_img_url' => '  https://example.com/img.jpg  ',
        'category_name' => '  Grãos  ',
        'sub_category_name' => '  Arroz  ',
        'line_name' => '  Premium  ',
        'brand_name' => '  Tio João  ',
        'sku_package' => '  SKU-001  ',
        'sku_package_name' => '  Pacote 5kg  ',
        'package_description' => "Descrição do pacote\nDetalhes",
        'gross_weight' => '5.5',
        'net_weight' => '5.0',
        'ean' => '  7891234567890  ',
        'package_img_url' => '  https://example.com/pkg.jpg  ',
    ]);

    expect($result['product_id'])->toBe('PROD-001')
        ->and($result['product_name'])->toBe('Arroz Tio João')
        ->and($result['product_description'])->toBe("Descrição completa\nCom quebra de linha")
        ->and($result['product_img_url'])->toBe('https://example.com/img.jpg')
        ->and($result['category_name'])->toBe('Grãos')
        ->and($result['sub_category_name'])->toBe('Arroz')
        ->and($result['line_name'])->toBe('Premium')
        ->and($result['brand_name'])->toBe('Tio João')
        ->and($result['sku_package'])->toBe('SKU-001')
        ->and($result['sku_package_name'])->toBe('Pacote 5kg')
        ->and($result['package_description'])->toBe("Descrição do pacote\nDetalhes")
        ->and($result['gross_weight'])->toBe(5.5)
        ->and($result['net_weight'])->toBe(5.0)
        ->and($result['ean'])->toBe('7891234567890')
        ->and($result['package_img_url'])->toBe('https://example.com/pkg.jpg');
});

it('handles a fully empty row correctly', function () {
    $result = $this->normalizer->normalize([
        'product_id' => '',
        'product_name' => '',
        'product_description' => '',
        'product_img_url' => '',
        'category_name' => '',
        'sub_category_name' => '',
        'line_name' => '',
        'brand_name' => '',
        'sku_package' => '',
        'sku_package_name' => '',
        'package_description' => '',
        'gross_weight' => '',
        'net_weight' => '',
        'ean' => '',
        'package_img_url' => '',
    ]);

    foreach ($result as $value) {
        expect($value)->toBeNull();
    }
});
