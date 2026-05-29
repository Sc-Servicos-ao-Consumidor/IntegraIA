<?php

use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use Illuminate\Support\Carbon;

// ============================================================
// Phase 1.3.1 — CatalogProductFactory
// ============================================================

it('creates a CatalogProduct with the factory', function () {
    $product = CatalogProduct::factory()->create();

    expect($product)->toBeInstanceOf(CatalogProduct::class)
        ->and($product->product_name)->not->toBeNull()
        ->and($product->codigo_padrao)->not->toBeNull()
        ->and($product->tenant_id)->not->toBeNull();
});

it('the embedded() state sets a non-null embedding and embedded_at', function () {
    $product = CatalogProduct::factory()->embedded()->create();

    expect($product->embedding)->not->toBeNull()
        ->and($product->embedded_at)->not->toBeNull()
        ->and($product->embedded_at)->toBeInstanceOf(Carbon::class);
});

// ============================================================
// Phase 1.3.2 — CatalogPackageFactory
// ============================================================

it('creates a CatalogPackage associated with a CatalogProduct', function () {
    $package = CatalogPackage::factory()->create();

    expect($package)->toBeInstanceOf(CatalogPackage::class)
        ->and($package->catalog_product_id)->not->toBeNull()
        ->and(CatalogProduct::find($package->catalog_product_id))->not->toBeNull();
});

// ============================================================
// Phase 1.3.3 — CatalogImportRunFactory
// ============================================================

it('creates a CatalogImportRun with default pending status', function () {
    CatalogImportStatus::firstOrCreate(['name' => 'pending']);

    $run = CatalogImportRun::factory()->create();

    expect($run)->toBeInstanceOf(CatalogImportRun::class)
        ->and($run->status->name)->toBe('pending');
});

it('the completed() state sets completed_at and the completed status', function () {
    CatalogImportStatus::firstOrCreate(['name' => 'pending']);
    CatalogImportStatus::firstOrCreate(['name' => 'completed']);

    $run = CatalogImportRun::factory()->completed()->create();

    expect($run->status->name)->toBe('completed')
        ->and($run->completed_at)->not->toBeNull()
        ->and($run->completed_at)->toBeInstanceOf(Carbon::class);
});
