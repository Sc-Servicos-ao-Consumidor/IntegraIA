<?php

namespace App\Services\Catalog;

use App\Models\CatalogProduct;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Ai\Embeddings;
use Pgvector\Laravel\Distance;

class CatalogSearchService
{
    public function search(string $query, int $tenantId, int $limit = 5): Collection
    {
        if (trim($query) === '') {
            throw new InvalidArgumentException('Query string cannot be empty.');
        }

        $response = Embeddings::for([$query])->dimensions(3072)->generate();
        $vector = $response->embeddings[0];

        return CatalogProduct::where('tenant_id', $tenantId)
            ->nearestNeighbors('embedding', $vector, Distance::Cosine)
            ->limit($limit)
            ->with('packages')
            ->get();
    }

    public function searchAsRagContext(string $query, int $tenantId, int $limit = 5): array
    {
        $results = $this->search($query, $tenantId, $limit);

        if ($results->isEmpty()) {
            return [];
        }

        return $results->map(fn (CatalogProduct $product) => [
            'codigo_padrao' => $product->codigo_padrao,
            'product_name' => $product->product_name,
            'product_description' => $product->product_description,
            'brand_name' => $product->brand_name,
            'category_name' => $product->category_name,
            'sub_category_name' => $product->sub_category_name,
            'line_name' => $product->line_name,
            'product_img_url' => $product->product_img_url,
            'packages' => $product->packages->map(fn ($package) => [
                'sku_package' => $package->sku_package,
                'sku_package_name' => $package->sku_package_name,
                'package_description' => $package->package_description,
                'gross_weight' => $package->gross_weight,
                'net_weight' => $package->net_weight,
                'ean' => $package->ean,
                'package_img_url' => $package->package_img_url,
            ])->toArray(),
        ])->toArray();
    }
}
