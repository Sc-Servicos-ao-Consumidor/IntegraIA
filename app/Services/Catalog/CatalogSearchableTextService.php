<?php

namespace App\Services\Catalog;

use App\Models\CatalogProduct;

class CatalogSearchableTextService
{
    public function build(CatalogProduct $product): string
    {
        if (! $product->relationLoaded('packages')) {
            $product->load('packages');
        }

        $parts = array_filter([
            $product->product_name,
            $product->product_description,
            $product->brand_name,
            $product->category_name,
            $product->sub_category_name,
            $product->line_name,
        ], fn ($value) => $value !== null && $value !== '');

        foreach ($product->packages as $package) {
            $packageParts = array_filter([
                $package->sku_package_name,
                $package->package_description,
                $package->ean,
            ], fn ($value) => $value !== null && $value !== '');

            array_push($parts, ...$packageParts);
        }

        return trim(implode(' ', $parts));
    }

    public function buildAndStore(CatalogProduct $product): void
    {
        $product->update(['searchable_text' => $this->build($product)]);
    }
}
