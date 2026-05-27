<?php

namespace Database\Factories;

use App\Models\CatalogImportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogImportStatus>
 */
class CatalogImportStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['pending', 'running', 'completed', 'failed']),
        ];
    }
}
