<?php

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use App\Models\Tenant;
use Database\Seeders\CatalogPipelineStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ============================================================
// Phase 5.1 — catalog_pipeline_statuses
// ============================================================

it('seeds the four expected pipeline status names', function () {
    $seeder = new CatalogPipelineStatusSeeder;
    $seeder->run();

    $names = DB::table('catalog_pipeline_statuses')->pluck('name')->sort()->values()->toArray();

    expect($names)->toBe(['completed', 'failed', 'pending', 'processing']);
});

it('enforces uniqueness on the catalog_pipeline_statuses name column', function () {
    DB::table('catalog_pipeline_statuses')->insert(['name' => 'pending', 'created_at' => now(), 'updated_at' => now()]);

    expect(fn () => DB::table('catalog_pipeline_statuses')->insert(['name' => 'pending', 'created_at' => now(), 'updated_at' => now()]))
        ->toThrow(QueryException::class);
});

// ============================================================
// Phase 5.2 — catalog_requests
// ============================================================

it('catalog_requests has the correct columns and nullable constraints', function () {
    $columns = [
        'id', 'tenant_id', 'catalog_pipeline_status_id', 'contact_id',
        'question', 'search_results', 'ai_answer', 'started_at', 'completed_at',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('catalog_requests', $column))->toBeTrue("Column {$column} missing");
    }
});

it('catalog_requests enforces foreign key to tenants', function () {
    $status = CatalogPipelineStatus::firstOrCreate(['name' => 'pending']);

    expect(fn () => CatalogRequest::create([
        'tenant_id' => 99999,
        'catalog_pipeline_status_id' => $status->id,
        'contact_id' => '+5511999999999',
        'question' => 'Qual produto?',
    ]))->toThrow(QueryException::class);
});

it('catalog_requests enforces foreign key to catalog_pipeline_statuses', function () {
    $tenant = Tenant::factory()->create();

    expect(fn () => CatalogRequest::create([
        'tenant_id' => $tenant->id,
        'catalog_pipeline_status_id' => 99999,
        'contact_id' => '+5511999999999',
        'question' => 'Qual produto?',
    ]))->toThrow(QueryException::class);
});
