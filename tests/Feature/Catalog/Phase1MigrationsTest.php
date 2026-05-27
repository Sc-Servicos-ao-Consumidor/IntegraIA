<?php

use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use Database\Seeders\CatalogImportStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ============================================================
// Phase 1.1.1 — catalog_import_statuses
// ============================================================

it('seeds the four expected import status names', function () {
    $seeder = new CatalogImportStatusSeeder;
    $seeder->run();

    $names = DB::table('catalog_import_statuses')->pluck('name')->sort()->values()->toArray();

    expect($names)->toBe(['completed', 'failed', 'pending', 'running']);
});

it('enforces uniqueness on the catalog_import_statuses name column', function () {
    DB::table('catalog_import_statuses')->insert(['name' => 'pending', 'created_at' => now(), 'updated_at' => now()]);

    expect(fn () => DB::table('catalog_import_statuses')->insert(['name' => 'pending', 'created_at' => now(), 'updated_at' => now()]))
        ->toThrow(QueryException::class);
});

// ============================================================
// Phase 1.1.2 — catalog_import_runs
// ============================================================

it('catalog_import_runs has the correct columns and nullable constraints', function () {
    expect(Schema::hasColumn('catalog_import_runs', 'id'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'tenant_id'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'catalog_import_status_id'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'file_name'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'total_rows'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'started_at'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_runs', 'completed_at'))->toBeTrue();
});

it('catalog_import_runs enforces foreign key to tenants', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);

    expect(fn () => CatalogImportRun::create([
        'tenant_id' => 99999,
        'catalog_import_status_id' => $status->id,
    ]))->toThrow(QueryException::class);
});

it('catalog_import_runs enforces foreign key to catalog_import_statuses', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => 99999,
    ]))->toThrow(QueryException::class);
});

// ============================================================
// Phase 1.1.3 — catalog_import_errors
// ============================================================

it('catalog_import_errors has the correct columns and nullable constraints', function () {
    expect(Schema::hasColumn('catalog_import_errors', 'id'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_errors', 'catalog_import_run_id'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_errors', 'row_number'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_errors', 'row_data'))->toBeTrue();
    expect(Schema::hasColumn('catalog_import_errors', 'error_message'))->toBeTrue();
});

it('catalog_import_errors enforces foreign key to catalog_import_runs', function () {
    expect(fn () => CatalogImportError::create([
        'catalog_import_run_id' => 99999,
        'error_message' => 'Test error',
    ]))->toThrow(QueryException::class);
});

// ============================================================
// Phase 1.1.4 — catalog_products
// ============================================================

it('catalog_products has the correct columns and nullable constraints', function () {
    $columns = [
        'id', 'tenant_id', 'codigo_padrao', 'sku', 'product_name',
        'product_description', 'product_img_url', 'category_name',
        'sub_category_name', 'line_name', 'brand_name',
        'searchable_text', 'embedding', 'embedded_at',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('catalog_products', $column))->toBeTrue("Column {$column} missing");
    }
});

it('catalog_products enforces the unique constraint on (tenant_id, codigo_padrao)', function () {
    $tenant = Tenant::factory()->create();

    CatalogProduct::create([
        'tenant_id' => $tenant->id,
        'codigo_padrao' => 'PROD-001',
        'product_name' => 'Product One',
    ]);

    expect(fn () => CatalogProduct::create([
        'tenant_id' => $tenant->id,
        'codigo_padrao' => 'PROD-001',
        'product_name' => 'Product One Duplicate',
    ]))->toThrow(QueryException::class);
});

it('catalog_products enforces foreign key to tenants', function () {
    expect(fn () => CatalogProduct::create([
        'tenant_id' => 99999,
        'codigo_padrao' => 'PROD-001',
        'product_name' => 'Product One',
    ]))->toThrow(QueryException::class);
});

// ============================================================
// Phase 1.1.5 — catalog_packages
// ============================================================

it('catalog_packages has the correct columns and nullable constraints', function () {
    $columns = [
        'id', 'catalog_product_id', 'sku_package', 'sku_package_name',
        'package_description', 'gross_weight', 'net_weight', 'ean', 'package_img_url',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('catalog_packages', $column))->toBeTrue("Column {$column} missing");
    }
});

it('catalog_packages enforces the unique constraint on (catalog_product_id, sku_package)', function () {
    $product = CatalogProduct::factory()->create();

    CatalogPackage::create([
        'catalog_product_id' => $product->id,
        'sku_package' => 'PKG-001',
    ]);

    expect(fn () => CatalogPackage::create([
        'catalog_product_id' => $product->id,
        'sku_package' => 'PKG-001',
    ]))->toThrow(QueryException::class);
});

it('catalog_packages enforces foreign key to catalog_products', function () {
    expect(fn () => CatalogPackage::create([
        'catalog_product_id' => 99999,
        'sku_package' => 'PKG-001',
    ]))->toThrow(QueryException::class);
});
