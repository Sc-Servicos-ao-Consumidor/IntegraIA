<?php

use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

// ============================================================
// Phase 1.2.1 — CatalogProduct
// ============================================================

it('CatalogProduct has the expected fillable attributes', function () {
    $fillable = (new CatalogProduct)->getFillable();

    expect($fillable)->toContain('tenant_id')
        ->toContain('codigo_padrao')
        ->toContain('sku')
        ->toContain('product_name')
        ->toContain('product_description')
        ->toContain('product_img_url')
        ->toContain('category_name')
        ->toContain('sub_category_name')
        ->toContain('line_name')
        ->toContain('brand_name')
        ->toContain('searchable_text')
        ->toContain('embedding')
        ->toContain('embedded_at');
});

it('CatalogProduct casts embedded_at to a datetime', function () {
    $product = CatalogProduct::factory()->create(['embedded_at' => now()]);

    expect($product->embedded_at)->toBeInstanceOf(Carbon::class);
});

it('CatalogProduct defines a hasMany relationship to CatalogPackage', function () {
    $product = CatalogProduct::factory()->create();

    expect($product->packages())->toBeInstanceOf(HasMany::class);
});

it('CatalogProduct defines a belongsTo relationship to Tenant', function () {
    $product = CatalogProduct::factory()->create();

    expect($product->tenant())->toBeInstanceOf(BelongsTo::class);
});

it('scopeWithoutEmbedding returns only records where embedding is null', function () {
    $productWithEmbedding = CatalogProduct::factory()->embedded()->create();
    $productWithoutEmbedding = CatalogProduct::factory()->create(['embedding' => null]);

    $results = CatalogProduct::withoutEmbedding()->where('tenant_id', $productWithoutEmbedding->tenant_id)->get();

    expect($results->pluck('id'))->toContain($productWithoutEmbedding->id)
        ->not->toContain($productWithEmbedding->id);
});

it('scopeStale returns records with null embedded_at or embedded_at < updated_at', function () {
    $tenant = Tenant::factory()->create();

    $neverEmbedded = CatalogProduct::factory()->create(['tenant_id' => $tenant->id, 'embedded_at' => null]);

    $staleProduct = CatalogProduct::factory()->create([
        'tenant_id' => $tenant->id,
        'embedded_at' => now()->subDay(),
    ]);
    $staleProduct->updateQuietly(['updated_at' => now()]);

    $freshProduct = CatalogProduct::factory()->embedded()->create(['tenant_id' => $tenant->id]);

    $staleIds = CatalogProduct::stale()->where('tenant_id', $tenant->id)->pluck('id');

    expect($staleIds)->toContain($neverEmbedded->id)
        ->toContain($staleProduct->id)
        ->not->toContain($freshProduct->id);
});

// ============================================================
// Phase 1.2.2 — CatalogPackage
// ============================================================

it('CatalogPackage has the expected fillable attributes', function () {
    $fillable = (new CatalogPackage)->getFillable();

    expect($fillable)->toContain('catalog_product_id')
        ->toContain('sku_package')
        ->toContain('sku_package_name')
        ->toContain('package_description')
        ->toContain('gross_weight')
        ->toContain('net_weight')
        ->toContain('ean')
        ->toContain('package_img_url');
});

it('CatalogPackage casts gross_weight and net_weight as decimals', function () {
    $package = CatalogPackage::factory()->create([
        'gross_weight' => 1.5,
        'net_weight' => 1.2,
    ]);

    expect($package->gross_weight)->toBeString()
        ->and($package->net_weight)->toBeString();
});

it('CatalogPackage defines a belongsTo relationship to CatalogProduct', function () {
    $package = CatalogPackage::factory()->create();

    expect($package->catalogProduct())->toBeInstanceOf(BelongsTo::class);
});

// ============================================================
// Phase 1.2.3 — CatalogImportStatus
// ============================================================

it('CatalogImportStatus has the expected fillable attributes', function () {
    expect((new CatalogImportStatus)->getFillable())->toContain('name');
});

it('CatalogImportStatus defines a hasMany relationship to CatalogImportRun', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);

    expect($status->runs())->toBeInstanceOf(HasMany::class);
});

// ============================================================
// Phase 1.2.4 — CatalogImportRun
// ============================================================

it('CatalogImportRun has the expected fillable attributes', function () {
    $fillable = (new CatalogImportRun)->getFillable();

    expect($fillable)->toContain('tenant_id')
        ->toContain('catalog_import_status_id')
        ->toContain('file_name')
        ->toContain('total_rows')
        ->toContain('created_count')
        ->toContain('updated_count')
        ->toContain('skipped_count')
        ->toContain('failed_count')
        ->toContain('started_at')
        ->toContain('completed_at');
});

it('CatalogImportRun casts started_at and completed_at as datetimes', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);
    $tenant = Tenant::factory()->create();

    $run = CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $status->id,
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    expect($run->started_at)->toBeInstanceOf(Carbon::class)
        ->and($run->completed_at)->toBeInstanceOf(Carbon::class);
});

it('CatalogImportRun defines a belongsTo relationship to CatalogImportStatus', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);
    $tenant = Tenant::factory()->create();

    $run = CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $status->id,
    ]);

    expect($run->status())->toBeInstanceOf(BelongsTo::class);
});

it('CatalogImportRun defines a hasMany relationship to CatalogImportError', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);
    $tenant = Tenant::factory()->create();

    $run = CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $status->id,
    ]);

    expect($run->errors())->toBeInstanceOf(HasMany::class);
});

// ============================================================
// Phase 1.2.5 — CatalogImportError
// ============================================================

it('CatalogImportError has the expected fillable attributes', function () {
    $fillable = (new CatalogImportError)->getFillable();

    expect($fillable)->toContain('catalog_import_run_id')
        ->toContain('row_number')
        ->toContain('row_data')
        ->toContain('error_message');
});

it('CatalogImportError casts row_data as array', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);
    $tenant = Tenant::factory()->create();
    $run = CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $status->id,
    ]);

    $error = CatalogImportError::create([
        'catalog_import_run_id' => $run->id,
        'row_data' => ['product_id' => 'X', 'name' => 'Test'],
        'error_message' => 'Invalid row',
    ]);

    expect($error->row_data)->toBeArray()
        ->and($error->row_data['product_id'])->toBe('X');
});

it('CatalogImportError defines a belongsTo relationship to CatalogImportRun', function () {
    $status = CatalogImportStatus::create(['name' => 'pending']);
    $tenant = Tenant::factory()->create();
    $run = CatalogImportRun::create([
        'tenant_id' => $tenant->id,
        'catalog_import_status_id' => $status->id,
    ]);

    $error = CatalogImportError::create([
        'catalog_import_run_id' => $run->id,
        'error_message' => 'Test error',
    ]);

    expect($error->run())->toBeInstanceOf(BelongsTo::class);
});
