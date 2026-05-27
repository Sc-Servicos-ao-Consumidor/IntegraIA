<?php

namespace Database\Factories;

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogRequest>
 */
class CatalogRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'catalog_pipeline_status_id' => fn () => CatalogPipelineStatus::firstOrCreate(['name' => 'pending'])->id,
            'contact_id' => fake()->numerify('+55119########'),
            'question' => fake()->sentence(8).'?',
            'search_results' => null,
            'ai_answer' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_pipeline_status_id' => fn () => CatalogPipelineStatus::firstOrCreate(['name' => 'processing'])->id,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_pipeline_status_id' => fn () => CatalogPipelineStatus::firstOrCreate(['name' => 'completed'])->id,
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'catalog_pipeline_status_id' => fn () => CatalogPipelineStatus::firstOrCreate(['name' => 'failed'])->id,
            'started_at' => now()->subMinutes(1),
            'completed_at' => now(),
        ]);
    }
}
