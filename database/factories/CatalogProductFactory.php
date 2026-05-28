<?php

namespace Database\Factories;

use App\Models\CatalogProduct;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Pgvector\Laravel\Vector;

/**
 * @extends Factory<CatalogProduct>
 */
class CatalogProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'codigo_padrao' => fake()->unique()->bothify('PROD-####??'),
            'sku' => fake()->optional()->bothify('SKU-####'),
            'product_name' => fake()->words(3, true),
            'product_description' => fake()->optional()->paragraph(),
            'product_img_url' => fake()->optional()->imageUrl(),
            'category_name' => fake()->optional()->word(),
            'sub_category_name' => fake()->optional()->word(),
            'line_name' => fake()->optional()->word(),
            'brand_name' => fake()->optional()->company(),
            'searchable_text' => null,
            'embedding' => null,
            'embedded_at' => null,
        ];
    }

    public function embedded(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding' => new Vector(array_fill(0, 1536, 1.0 / sqrt(1536))),
            'embedded_at' => now(),
        ]);
    }
}
