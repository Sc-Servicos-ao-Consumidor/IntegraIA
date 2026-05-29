<?php

use App\Imports\CatalogImport;
use App\Services\Catalog\CatalogFieldNormalizer;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function makeXlsxFile(array $headers, array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([$headers, ...$rows], null, 'A1');

    $path = tempnam(sys_get_temp_dir(), 'catalog_test_').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

function makeValidRow(array $overrides = []): array
{
    return array_merge([
        'PROD-001',
        'Arroz Tio João',
        'Descrição do produto',
        'https://example.com/img.jpg',
        'Grãos',
        'Arroz',
        'Premium',
        'Tio João',
        'SKU-001',
        'Pacote 5kg',
        'Descrição do pacote',
        '5.5',
        '5.0',
        '7891234567890',
        'https://example.com/pkg.jpg',
    ], $overrides);
}

$xlsHeaders = [
    'product_id', 'product_name', 'product_description', 'product_img_url',
    'category_name', 'sub_category_name', 'line_name', 'brand_name',
    'sku_package', 'sku_package_name', 'package_description',
    'gross_weight', 'net_weight', 'ean', 'package_img_url',
];

it('reads all expected columns from a valid XLS file', function () use ($xlsHeaders) {
    $file = makeXlsxFile($xlsHeaders, [makeValidRow()]);
    $import = new CatalogImport(new CatalogFieldNormalizer);

    Excel::import($import, $file);

    $rows = $import->getRows();

    expect($rows)->toHaveCount(1);

    $row = $rows->first();
    expect($row)->toHaveKey('product_id')
        ->toHaveKey('product_name')
        ->toHaveKey('product_description')
        ->toHaveKey('product_img_url')
        ->toHaveKey('category_name')
        ->toHaveKey('sub_category_name')
        ->toHaveKey('line_name')
        ->toHaveKey('brand_name')
        ->toHaveKey('sku_package')
        ->toHaveKey('sku_package_name')
        ->toHaveKey('package_description')
        ->toHaveKey('gross_weight')
        ->toHaveKey('net_weight')
        ->toHaveKey('ean')
        ->toHaveKey('package_img_url');

    unlink($file);
});

it('skips rows with empty product_id', function () use ($xlsHeaders) {
    $rowWithEmptyProductId = makeValidRow();
    $rowWithEmptyProductId[0] = '';

    $file = makeXlsxFile($xlsHeaders, [$rowWithEmptyProductId]);
    $import = new CatalogImport(new CatalogFieldNormalizer);

    Excel::import($import, $file);

    expect($import->getRows())->toHaveCount(0);

    unlink($file);
});

it('skips rows with empty sku_package', function () use ($xlsHeaders) {
    $rowWithEmptySku = makeValidRow();
    $rowWithEmptySku[8] = '';

    $file = makeXlsxFile($xlsHeaders, [$rowWithEmptySku]);
    $import = new CatalogImport(new CatalogFieldNormalizer);

    Excel::import($import, $file);

    expect($import->getRows())->toHaveCount(0);

    unlink($file);
});

it('normalizes fields on each valid row', function () use ($xlsHeaders) {
    $rowWithSpaces = makeValidRow();
    $rowWithSpaces[1] = '  Arroz com Espaços  ';
    $rowWithSpaces[4] = '  Grãos  ';

    $file = makeXlsxFile($xlsHeaders, [$rowWithSpaces]);
    $import = new CatalogImport(new CatalogFieldNormalizer);

    Excel::import($import, $file);

    $row = $import->getRows()->first();

    expect($row['product_name'])->toBe('Arroz com Espaços')
        ->and($row['category_name'])->toBe('Grãos');

    unlink($file);
});
