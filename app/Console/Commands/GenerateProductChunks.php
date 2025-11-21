<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\PrismService;
use App\Services\ProductChunkService;
use Illuminate\Console\Command;

class GenerateProductChunks extends Command
{
    protected $signature = 'embedding:chunk-products {--limit=} {--force}';

    protected $description = 'Generate chunked embeddings for products';

    public function handle()
    {
        $this->info('Starting product chunk generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        $service = new ProductChunkService(new PrismService);

        $query = Product::query();
        if (! $force) {
            $query->whereDoesntHave('chunks');
        }
        if ($limit) {
            $query->limit((int) $limit);
        }

        $products = $query->get();
        if ($products->isEmpty()) {
            $this->info('No products to process.');

            return Command::SUCCESS;
        }

        $progress = $this->output->createProgressBar($products->count());
        $progress->start();
        $created = 0;

        foreach ($products as $product) {
            $created += $service->generateChunks($product);
            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("Created {$created} chunks across {$products->count()} products.");

        return Command::SUCCESS;
    }
}
