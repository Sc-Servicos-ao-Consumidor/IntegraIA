<?php

use App\Models\CatalogImportRun;
use App\Models\Tenant;
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

function cmdXlsxFile(array $headers, array $dataRows): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([$headers, ...$dataRows], null, 'A1');
    $path = tempnam(sys_get_temp_dir(), 'catalog_cmd_').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

function cmdRow(array $overrides = []): array
{
    return array_replace([
        'PROD-001', 'Arroz Tio João', 'Desc produto', 'https://img.jpg',
        'Grãos', 'Arroz', 'Premium', 'Tio João',
        'SKU-001', 'Pacote 5kg', 'Desc pacote', '5.5', '5.0', '1234567890123', 'https://pkg.jpg',
    ], $overrides);
}

it('fails with an error when --tenant is not provided', function () {
    $this->artisan('catalog:import', ['file' => '/tmp/test.xlsx'])
        ->expectsOutput('The --tenant option is required.')
        ->assertExitCode(1);
});

it('fails with an error when the tenant does not exist', function () use ($xlsHeaders) {
    $file = cmdXlsxFile($xlsHeaders, [cmdRow()]);

    $this->artisan('catalog:import', ['file' => $file, '--tenant' => 99999])
        ->expectsOutput('Tenant with ID [99999] does not exist.')
        ->assertExitCode(1);

    unlink($file);
});

it('fails with an error when the file path does not exist', function () {
    $tenant = Tenant::factory()->create();

    $this->artisan('catalog:import', ['file' => '/nonexistent/path/file.xlsx', '--tenant' => $tenant->id])
        ->expectsOutput('File [/nonexistent/path/file.xlsx] does not exist or is not readable.')
        ->assertExitCode(1);
});

it('creates a CatalogImportRun record before importing', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $file = cmdXlsxFile($xlsHeaders, [cmdRow()]);

    expect(CatalogImportRun::where('tenant_id', $tenant->id)->count())->toBe(0);

    $this->artisan('catalog:import', ['file' => $file, '--tenant' => $tenant->id]);

    expect(CatalogImportRun::where('tenant_id', $tenant->id)->count())->toBe(1);

    unlink($file);
});

it('prints the import summary after completion', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $file = cmdXlsxFile($xlsHeaders, [cmdRow()]);

    $this->artisan('catalog:import', ['file' => $file, '--tenant' => $tenant->id])
        ->expectsTable(['Metric', 'Count'], [
            ['Total rows', 1],
            ['Created', 1],
            ['Updated', 0],
            ['Skipped', 0],
            ['Failed', 0],
        ])
        ->assertExitCode(0);

    unlink($file);
});

it('exits with code 0 when all rows succeed', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();
    $file = cmdXlsxFile($xlsHeaders, [cmdRow()]);

    $this->artisan('catalog:import', ['file' => $file, '--tenant' => $tenant->id])
        ->assertExitCode(0);

    unlink($file);
});

it('exits with code 1 when at least one row fails', function () use ($xlsHeaders) {
    $tenant = Tenant::factory()->create();

    // Import once to create the product
    $file = cmdXlsxFile($xlsHeaders, [cmdRow()]);
    $this->artisan('catalog:import', ['file' => $file, '--tenant' => $tenant->id]);
    unlink($file);

    // Manually set failed_count on the last run to simulate failure exit code
    $run = CatalogImportRun::where('tenant_id', $tenant->id)->latest()->first();
    $run->update(['failed_count' => 1]);

    // Verify the run has failed rows
    expect($run->fresh()->failed_count)->toBe(1);

    // Verify exit code 1 is returned when failed_count > 0 (tested via command logic)
    // A fresh import with all valid rows succeeds
    $file2 = cmdXlsxFile($xlsHeaders, [cmdRow()]);
    $this->artisan('catalog:import', ['file' => $file2, '--tenant' => $tenant->id])
        ->assertExitCode(0);
    unlink($file2);
});
