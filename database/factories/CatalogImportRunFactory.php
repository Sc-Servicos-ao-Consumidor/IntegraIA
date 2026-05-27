<?php

namespace Database\Factories;

use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogImportRun>
 */
class CatalogImportRunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'catalog_import_status_id' => fn () => CatalogImportStatus::firstOrCreate(['name' => 'pending'])->id,
            'file_name' => null,
            'total_rows' => 0,
            'created_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_import_status_id' => fn () => CatalogImportStatus::firstOrCreate(['name' => 'running'])->id,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_import_status_id' => fn () => CatalogImportStatus::firstOrCreate(['name' => 'completed'])->id,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_import_status_id' => fn () => CatalogImportStatus::firstOrCreate(['name' => 'failed'])->id,
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }
}
