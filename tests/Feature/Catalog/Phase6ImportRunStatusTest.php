<?php

use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\Tenant;
use App\Services\Catalog\CatalogImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

$p6ImportHeaders = [
    'product_id', 'product_name', 'product_description', 'product_img_url',
    'category_name', 'sub_category_name', 'line_name', 'brand_name',
    'sku_package', 'sku_package_name', 'package_description',
    'gross_weight', 'net_weight', 'ean', 'package_img_url',
];

function phase6ImportXlsxFile(array $headers, array $dataRows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([$headers, ...$dataRows], null, 'A1');
    $path = tempnam(sys_get_temp_dir(), 'catalog_p6i_').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

function phase6ImportRow(array $overrides = []): array
{
    return array_replace([
        'PROD-P6-001', 'Arroz Tio João', 'Desc produto', 'https://img.jpg',
        'Grãos', 'Arroz', 'Premium', 'Tio João',
        'SKU-P6-001', 'Pacote 5kg', 'Desc pacote', '5.5', '5.0', '1234567890123', 'https://pkg.jpg',
    ], $overrides);
}

// ─── Phase 6.1 — Import Run Status Tracking ───────────────────────────────────

it('transitions import run from pending to running on start', function () use ($p6ImportHeaders) {
    $tenant = Tenant::factory()->create();
    $pendingStatus = CatalogImportStatus::firstOrCreate(['name' => 'pending']);

    $run = CatalogImportRun::factory()->create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $pendingStatus->id,
        'started_at' => null,
    ]);

    expect($run->started_at)->toBeNull()
        ->and($run->status->name)->toBe('pending');

    $file = phase6ImportXlsxFile($p6ImportHeaders, [phase6ImportRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $run->refresh();

    expect($run->started_at)->not->toBeNull()
        ->and($run->status->name)->not->toBe('pending');

    unlink($file);
});

it('transitions import run to completed after a successful import', function () use ($p6ImportHeaders) {
    $tenant = Tenant::factory()->create();
    $run = CatalogImportRun::factory()->create(['tenant_id' => $tenant->id]);

    $file = phase6ImportXlsxFile($p6ImportHeaders, [phase6ImportRow()]);

    (new CatalogImportService($tenant->id))->import($file, $run);

    $run->refresh();

    expect($run->status->name)->toBe('completed')
        ->and($run->completed_at)->not->toBeNull();

    unlink($file);
});

it('transitions import run to failed when a fatal error occurs', function () {
    $tenant = Tenant::factory()->create();
    $pendingStatus = CatalogImportStatus::firstOrCreate(['name' => 'pending']);

    $run = CatalogImportRun::factory()->create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $pendingStatus->id,
    ]);

    Excel::shouldReceive('import')->andThrow(new RuntimeException('Fatal import error'));

    try {
        (new CatalogImportService($tenant->id))->import('/tmp/fake.xlsx', $run);
    } catch (RuntimeException) {
        // expected — service re-throws after transitioning to failed
    }

    $run->refresh();

    expect($run->status->name)->toBe('failed')
        ->and($run->completed_at)->not->toBeNull();
});

it('stores row_number and row_data on CatalogImportError for failed rows', function () use ($p6ImportHeaders) {
    $tenant = Tenant::factory()->create();
    $run = CatalogImportRun::factory()->create(['tenant_id' => $tenant->id]);

    $file = phase6ImportXlsxFile($p6ImportHeaders, [phase6ImportRow()]);

    try {
        Event::listen('eloquent.creating: '.CatalogPackage::class, function () {
            throw new RuntimeException('Forced package failure for row_number test');
        });

        (new CatalogImportService($tenant->id))->import($file, $run);
    } finally {
        Event::forget('eloquent.creating: '.CatalogPackage::class);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $error = CatalogImportError::where('catalog_import_run_id', $run->id)->first();

    expect($error)->not->toBeNull()
        ->and($error->row_number)->not->toBeNull()
        ->and($error->row_number)->toBeInt()
        ->and($error->row_data)->toBeArray();
});

it('stores the original error message on CatalogImportError', function () use ($p6ImportHeaders) {
    $tenant = Tenant::factory()->create();
    $run = CatalogImportRun::factory()->create(['tenant_id' => $tenant->id]);

    $file = phase6ImportXlsxFile($p6ImportHeaders, [phase6ImportRow()]);
    $expectedMessage = 'Specific package failure message for Phase 6 test';

    try {
        Event::listen('eloquent.creating: '.CatalogPackage::class, function () use ($expectedMessage) {
            throw new RuntimeException($expectedMessage);
        });

        (new CatalogImportService($tenant->id))->import($file, $run);
    } finally {
        Event::forget('eloquent.creating: '.CatalogPackage::class);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $error = CatalogImportError::where('catalog_import_run_id', $run->id)->first();

    expect($error->error_message)->toBe($expectedMessage);
});
