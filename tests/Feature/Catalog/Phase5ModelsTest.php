<?php

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

// ============================================================
// Phase 5.3 — CatalogPipelineStatus model
// ============================================================

it('CatalogPipelineStatus has the expected fillable attributes', function () {
    expect((new CatalogPipelineStatus)->getFillable())->toContain('name');
});

it('CatalogPipelineStatus defines a hasMany relationship to CatalogRequest', function () {
    $status = CatalogPipelineStatus::firstOrCreate(['name' => 'pending']);

    expect($status->runs())->toBeInstanceOf(HasMany::class);
});

// ============================================================
// Phase 5.3 — CatalogRequest model
// ============================================================

it('CatalogRequest has the expected fillable attributes', function () {
    $fillable = (new CatalogRequest)->getFillable();

    expect($fillable)->toContain('tenant_id')
        ->toContain('catalog_pipeline_status_id')
        ->toContain('contact_id')
        ->toContain('question')
        ->toContain('search_results')
        ->toContain('ai_answer')
        ->toContain('started_at')
        ->toContain('completed_at');
});

it('CatalogRequest casts search_results and ai_answer as arrays', function () {
    $request = CatalogRequest::factory()->create([
        'search_results' => [['product_name' => 'Produto A']],
        'ai_answer' => ['answer' => 'Resposta', 'products' => []],
    ]);

    expect($request->search_results)->toBeArray()
        ->and($request->ai_answer)->toBeArray();
});

it('CatalogRequest casts started_at and completed_at as datetimes', function () {
    $request = CatalogRequest::factory()->create([
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    expect($request->started_at)->toBeInstanceOf(Carbon::class)
        ->and($request->completed_at)->toBeInstanceOf(Carbon::class);
});

it('CatalogRequest defines a belongsTo relationship to CatalogPipelineStatus', function () {
    $request = CatalogRequest::factory()->create();

    expect($request->status())->toBeInstanceOf(BelongsTo::class);
});

it('CatalogRequest defines a belongsTo relationship to Tenant', function () {
    $request = CatalogRequest::factory()->create();

    expect($request->tenant())->toBeInstanceOf(BelongsTo::class);
});

// ============================================================
// Phase 5.3 — CatalogRequestFactory
// ============================================================

it('the factory creates a CatalogRequest with pending status by default', function () {
    $request = CatalogRequest::factory()->create();

    expect($request->status->name)->toBe('pending');
});

it('the completed() factory state sets completed_at', function () {
    $request = CatalogRequest::factory()->completed()->create();

    expect($request->completed_at)->not->toBeNull()
        ->and($request->completed_at)->toBeInstanceOf(Carbon::class)
        ->and($request->status->name)->toBe('completed');
});
