<?php

namespace App\Jobs;

use App\Models\CatalogProduct;
use App\Services\Catalog\CatalogSearchableTextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

class GenerateCatalogProductEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogProductId) {}

    public function handle(CatalogSearchableTextService $textService): void
    {
        $product = CatalogProduct::find($this->catalogProductId);

        if (! $product) {
            return;
        }

        $textService->buildAndStore($product);
        $product->refresh();

        if (empty($product->searchable_text)) {
            Log::warning('GenerateCatalogProductEmbedding: skipping empty searchable_text', [
                'catalog_product_id' => $this->catalogProductId,
            ]);

            return;
        }

        try {
            $response = Embeddings::for([$product->searchable_text])->dimensions(3072)->generate();

            $product->update([
                'embedding' => $response->embeddings[0],
                'embedded_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateCatalogProductEmbedding: failed to generate embedding', [
                'catalog_product_id' => $this->catalogProductId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
