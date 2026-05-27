<?php

namespace Database\Factories;

use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogImportError>
 */
class CatalogImportErrorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'catalog_import_run_id' => CatalogImportRun::factory(),
            'row_number' => fake()->optional()->numberBetween(1, 1000),
            'row_data' => fake()->optional()->passthrough(['field' => fake()->word()]),
            'error_message' => fake()->sentence(),
        ];
    }
}
