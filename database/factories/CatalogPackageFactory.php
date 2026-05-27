<?php

namespace Database\Factories;

use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogPackage>
 */
class CatalogPackageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'catalog_product_id' => CatalogProduct::factory(),
            'sku_package' => fake()->unique()->bothify('PKG-####??'),
            'sku_package_name' => fake()->optional()->words(2, true),
            'package_description' => fake()->optional()->sentence(),
            'gross_weight' => fake()->optional()->randomFloat(4, 0.1, 50.0),
            'net_weight' => fake()->optional()->randomFloat(4, 0.1, 50.0),
            'ean' => fake()->optional()->ean13(),
            'package_img_url' => fake()->optional()->imageUrl(),
        ];
    }
}
