<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\EmbeddingService;
use App\Services\PrismService;
use Illuminate\Console\Command;

class GenerateProductEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embedding:generate-products 
                            {--limit= : Limit the number of products to process}
                            {--force : Regenerate embeddings even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for products using the AI service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting product embedding generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        try {
            $embeddingService = new EmbeddingService(new PrismService());

            $query = Product::query();

            if (!$force) {
                $query->whereNull('embedding');
            }

            if ($limit) {
                $query->limit((int) $limit);
            }

            $products = $query->get();

            if ($products->isEmpty()) {
                $this->info('No products to process.');
                return Command::SUCCESS;
            }

            $this->info("Processing {$products->count()} products...");

            $progressBar = $this->output->createProgressBar($products->count());
            $progressBar->start();

            $successCount = 0;
            $errorCount = 0;

            foreach ($products as $product) {
                if ($embeddingService->generateEmbedding($product)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $this->warn("\nFailed to generate embedding for product ID: {$product->id}");
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();

            $this->newLine(2);
            $this->info("Product embedding generation completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Success', $successCount],
                    ['Failed', $errorCount],
                    ['Total', $products->count()]
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error generating product embeddings: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}