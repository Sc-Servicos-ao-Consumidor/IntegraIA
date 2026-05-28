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

        $parts = [];

        if (! empty($product->product_name)) {
            $parts[] = "Produto: {$product->product_name}";
        }

        if (! empty($product->brand_name)) {
            $parts[] = "Marca: {$product->brand_name}";
        }

        if (! empty($product->sub_category_name)) {
            $parts[] = "Subcategoria: {$product->sub_category_name}";
        }

        if (! empty($product->line_name)) {
            $parts[] = "Linha: {$product->line_name}";
        }

        $packages = $product->packages
            ->pluck('sku_package_name')
            ->filter()
            ->unique()
            ->values();

        if ($packages->isNotEmpty()) {
            $parts[] = 'Embalagens disponíveis: '.$packages->implode('; ');
        }

        return trim(implode("\n", $parts));
    }

    public function buildAndStore(CatalogProduct $product): void
    {
        $product->update(['searchable_text' => $this->build($product)]);
    }
}
