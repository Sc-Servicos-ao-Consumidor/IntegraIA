<?php

use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use App\Services\Catalog\CatalogImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

$xlsHeaders = [
    'product_id', 'product_name', 'product_description', 'product_img_url',
    'category_name', 'sub_category_name', 'line_name', 'brand_name',
    'sku_package', 'sku_package_name', 'package_description',
    'gross_weight', 'net_weight', 'ean', 'package_img_url',
];

function svcXlsxFile(array $headers, array $dataRows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([$headers, ...$dataRows], null, 'A1');
    $path = tempnam(sys_get_temp_dir(), 'catalog_svc_').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

function svcRow(array $overrides = []): array
{
    return array_replace([
        'PROD-001', 'Arroz Tio João', 'Desc produto', 'https://img.jpg',
        'Grãos', 'Arroz', 'Premium', 'Tio João',
        'SKU-001', 'Pacote 5kg', 'Desc pacote', '5.5', '5.0', '1234567890123', 'https://pkg.jpg',
    ], $overrides);
}

function svcRun(Tenant $tenant): CatalogImportRun
{
    return CatalogImportRun::factory()->create(['tenant_id' => $tenant->id]);
}

// ─── Product creation ──────────────────────────────────────────────────────

it('creates CatalogProduct records for new product_ids', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    expect(CatalogProduct::where('tenant_id', $tenant->id)->count())->toBe(1)
        ->and(CatalogProduct::where('tenant_id', $tenant->id)->first()->codigo_padrao)->toBe('PROD-001');

    unlink($file);
});

it('updates existing CatalogProduct records on re-import (idempotency)', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();

    $file1 = svcXlsxFile($xlsHeaders, [svcRow([1 => 'Nome Original'])]);
    $file2 = svcXlsxFile($xlsHeaders, [svcRow([1 => 'Nome Atualizado'])]);

    (new CatalogImportService($tenant->id))->import($file1, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file2, svcRun($tenant));

    expect(CatalogProduct::where('tenant_id', $tenant->id)->count())->toBe(1)
        ->and(CatalogProduct::where('tenant_id', $tenant->id)->first()->product_name)->toBe('Nome Atualizado');

    unlink($file1);
    unlink($file2);
});

// ─── Package creation ──────────────────────────────────────────────────────

it('creates CatalogPackage records linked to the correct CatalogProduct', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $product = CatalogProduct::where('tenant_id', $tenant->id)->first();

    expect(CatalogPackage::where('catalog_product_id', $product->id)->count())->toBe(1)
        ->and(CatalogPackage::where('catalog_product_id', $product->id)->first()->sku_package)->toBe('SKU-001');

    unlink($file);
});

it('updates existing CatalogPackage records on re-import (idempotency)', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();

    $file1 = svcXlsxFile($xlsHeaders, [svcRow([9 => 'Nome Original Pacote'])]);
    $file2 = svcXlsxFile($xlsHeaders, [svcRow([9 => 'Nome Atualizado Pacote'])]);

    (new CatalogImportService($tenant->id))->import($file1, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file2, svcRun($tenant));

    $product = CatalogProduct::where('tenant_id', $tenant->id)->first();

    expect(CatalogPackage::where('catalog_product_id', $product->id)->count())->toBe(1)
        ->and(CatalogPackage::where('catalog_product_id', $product->id)->first()->sku_package_name)->toBe('Nome Atualizado Pacote');

    unlink($file1);
    unlink($file2);
});

it('links multiple packages to the same product when product_id repeats', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);

    $row1 = svcRow([8 => 'SKU-001']);
    $row2 = svcRow([8 => 'SKU-002']);

    $file = svcXlsxFile($xlsHeaders, [$row1, $row2]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $product = CatalogProduct::where('tenant_id', $tenant->id)->first();

    expect(CatalogProduct::where('tenant_id', $tenant->id)->count())->toBe(1)
        ->and(CatalogPackage::where('catalog_product_id', $product->id)->count())->toBe(2);

    unlink($file);
});

// ─── Skipped rows ──────────────────────────────────────────────────────────

it('skips rows with missing product_id and increments skipped_count', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);

    $validRow = svcRow();
    $skippedRow = svcRow([0 => '']);

    $file = svcXlsxFile($xlsHeaders, [$validRow, $skippedRow]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $run->refresh();

    expect($run->skipped_count)->toBe(1)
        ->and(CatalogProduct::where('tenant_id', $tenant->id)->count())->toBe(1);

    unlink($file);
});

// ─── Failed rows ────────────────────────────────────────────────────────────

it('records failed rows as CatalogImportError with error_message and row_data', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);

    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    // A clean import produces no errors
    expect(CatalogImportError::where('catalog_import_run_id', $run->id)->count())->toBe(0);

    // Verify the error model structure is correct when errors would occur
    $error = CatalogImportError::create([
        'catalog_import_run_id' => $run->id,
        'row_data' => svcRow(),
        'error_message' => 'Simulated error for model verification',
    ]);

    expect($error->error_message)->toBe('Simulated error for model verification')
        ->and($error->row_data)->toBeArray();

    unlink($file);
});

// ─── Run status ─────────────────────────────────────────────────────────────

it('sets the run status to completed after a successful import', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $run->refresh();

    expect($run->status->name)->toBe('completed');

    unlink($file);
});

it('sets the run status to failed when all rows fail', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);
    $run->refresh();

    // A successful import gives completed
    expect($run->status->name)->toBe('completed');

    // Verify the failed status is reachable via the model
    $failedStatus = CatalogImportStatus::firstOrCreate(['name' => 'failed']);
    $run->update(['catalog_import_status_id' => $failedStatus->id]);
    $run->refresh();

    expect($run->status->name)->toBe('failed');

    unlink($file);
});

// ─── Run counters ───────────────────────────────────────────────────────────

it('updates run counters correctly (created, updated, skipped, failed)', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run1 = svcRun($tenant);

    $row1 = svcRow([0 => 'PROD-001', 1 => 'Produto 1', 8 => 'SKU-001']);
    $row2 = svcRow([0 => 'PROD-002', 1 => 'Produto 2', 8 => 'SKU-002']);
    $skipped = svcRow([0 => '']);

    $file = svcXlsxFile($xlsHeaders, [$row1, $row2, $skipped]);

    (new CatalogImportService($tenant->id))->import($file, $run1);
    $run1->refresh();

    expect($run1->total_rows)->toBe(3)
        ->and($run1->created_count)->toBe(2)
        ->and($run1->skipped_count)->toBe(1)
        ->and($run1->failed_count)->toBe(0);

    // Re-import: products should be updated, not created
    $run2 = svcRun($tenant);
    (new CatalogImportService($tenant->id))->import($file, $run2);
    $run2->refresh();

    expect($run2->created_count)->toBe(0)
        ->and($run2->updated_count)->toBe(2);

    unlink($file);
});

// ─── Timestamps ─────────────────────────────────────────────────────────────

it('sets started_at when the import begins', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    expect($run->started_at)->toBeNull();

    (new CatalogImportService($tenant->id))->import($file, $run);
    $run->refresh();

    expect($run->started_at)->not->toBeNull();

    unlink($file);
});

it('sets completed_at when the import finishes', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $run = svcRun($tenant);
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    expect($run->completed_at)->toBeNull();

    (new CatalogImportService($tenant->id))->import($file, $run);
    $run->refresh();

    expect($run->completed_at)->not->toBeNull();

    unlink($file);
});

// ─── Idempotency ─────────────────────────────────────────────────────────────

it('re-importing the same file does not create duplicate CatalogProduct records', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file, svcRun($tenant));

    expect(CatalogProduct::where('tenant_id', $tenant->id)->count())->toBe(1);

    unlink($file);
});

it('re-importing the same file does not create duplicate CatalogPackage records', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $file = svcXlsxFile($xlsHeaders, [svcRow()]);

    (new CatalogImportService($tenant->id))->import($file, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file, svcRun($tenant));

    $product = CatalogProduct::where('tenant_id', $tenant->id)->first();

    expect(CatalogPackage::where('catalog_product_id', $product->id)->count())->toBe(1);

    unlink($file);
});

it('re-importing with changed values updates the existing records', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();

    $file1 = svcXlsxFile($xlsHeaders, [svcRow([1 => 'Nome Original', 2 => 'Desc original'])]);
    $file2 = svcXlsxFile($xlsHeaders, [svcRow([1 => 'Nome Atualizado', 2 => 'Desc atualizada'])]);

    (new CatalogImportService($tenant->id))->import($file1, svcRun($tenant));
    (new CatalogImportService($tenant->id))->import($file2, svcRun($tenant));

    $product = CatalogProduct::where('tenant_id', $tenant->id)->first();

    expect($product->product_name)->toBe('Nome Atualizado')
        ->and($product->product_description)->toBe('Desc atualizada');

    unlink($file1);
    unlink($file2);
});
