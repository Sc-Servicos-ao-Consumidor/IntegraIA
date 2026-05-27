<?php

use App\Jobs\GenerateCatalogProductEmbedding;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

// ============================================================
// Phase 3.3 — catalog:embed Artisan Command
// ============================================================

it('dispatches jobs only for records without an embedding by default', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();

    $withoutEmbedding = CatalogProduct::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    CatalogProduct::factory()->embedded()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->artisan('catalog:embed', ['--tenant' => $tenant->id])
        ->assertExitCode(0);

    Queue::assertPushed(GenerateCatalogProductEmbedding::class, 3);

    foreach ($withoutEmbedding as $product) {
        Queue::assertPushed(
            GenerateCatalogProductEmbedding::class,
            fn ($job) => $job->catalogProductId === $product->id
        );
    }
});

it('dispatches jobs for stale records when --refresh is passed', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();

    $stale = CatalogProduct::factory()->create([
        'tenant_id' => $tenant->id,
        'embedded_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now(),
    ]);

    $fresh = CatalogProduct::factory()->embedded()->create([
        'tenant_id' => $tenant->id,
    ]);
    $fresh->update(['embedded_at' => Carbon::now()->addSecond()]);

    CatalogProduct::factory()->create([
        'tenant_id' => $tenant->id,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    $this->artisan('catalog:embed', ['--tenant' => $tenant->id, '--refresh' => true])
        ->assertExitCode(0);

    Queue::assertPushed(
        GenerateCatalogProductEmbedding::class,
        fn ($job) => $job->catalogProductId === $stale->id
    );

    Queue::assertNotPushed(
        GenerateCatalogProductEmbedding::class,
        fn ($job) => $job->catalogProductId === $fresh->id
    );
});

it('does not dispatch jobs for already-embedded non-stale records without --refresh', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();

    $embedded = CatalogProduct::factory()->embedded()->create([
        'tenant_id' => $tenant->id,
    ]);
    $embedded->update(['embedded_at' => Carbon::now()->addSecond()]);

    $this->artisan('catalog:embed', ['--tenant' => $tenant->id])
        ->assertExitCode(0);

    Queue::assertNotPushed(
        GenerateCatalogProductEmbedding::class,
        fn ($job) => $job->catalogProductId === $embedded->id
    );
});

it('fails with an error when --tenant is not provided', function () {
    $this->artisan('catalog:embed')
        ->expectsOutput('The --tenant option is required.')
        ->assertExitCode(1);
});

it('fails with an error when the tenant does not exist', function () {
    $this->artisan('catalog:embed', ['--tenant' => 99999])
        ->expectsOutput('Tenant with ID [99999] does not exist.')
        ->assertExitCode(1);
});

it('outputs the count of queued jobs', function () {
    Queue::fake();

    $tenant = Tenant::factory()->create();

    CatalogProduct::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'embedding' => null,
        'embedded_at' => null,
    ]);

    $this->artisan('catalog:embed', ['--tenant' => $tenant->id])
        ->expectsOutput('2 record(s) queued for embedding.')
        ->assertExitCode(0);
});
