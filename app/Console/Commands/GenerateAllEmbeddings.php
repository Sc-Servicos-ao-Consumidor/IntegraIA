<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use App\Services\EmbeddingService;
use App\Services\PrismService;
use Illuminate\Console\Command;

class GenerateAllEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embedding:generate-all 
                            {--models=* : Specific models to process (recipes,products,content)}
                            {--limit= : Limit the number of items to process per model}
                            {--force : Regenerate embeddings even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all models (recipes, products, content) using the AI service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting embedding generation for all models...');

        $models = $this->option('models');
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Default to all models if none specified
        if (empty($models)) {
            $models = ['recipes', 'products', 'content'];
        }

        try {
            $embeddingService = new EmbeddingService(new PrismService());
            $totalStats = ['success' => 0, 'failed' => 0, 'total' => 0];

            if (in_array('recipes', $models)) {
                $this->info("\n=== Processing Recipes ===");
                $stats = $this->processModel(Recipe::class, $embeddingService, $limit, $force);
                $this->displayStats('Recipes', $stats);
                $totalStats['success'] += $stats['success'];
                $totalStats['failed'] += $stats['failed'];
                $totalStats['total'] += $stats['total'];
            }

            if (in_array('products', $models)) {
                $this->info("\n=== Processing Products ===");
                $stats = $this->processModel(Product::class, $embeddingService, $limit, $force);
                $this->displayStats('Products', $stats);
                $totalStats['success'] += $stats['success'];
                $totalStats['failed'] += $stats['failed'];
                $totalStats['total'] += $stats['total'];
            }

            if (in_array('content', $models)) {
                $this->info("\n=== Processing Content ===");
                $stats = $this->processModel(Content::class, $embeddingService, $limit, $force);
                $this->displayStats('Content', $stats);
                $totalStats['success'] += $stats['success'];
                $totalStats['failed'] += $stats['failed'];
                $totalStats['total'] += $stats['total'];
            }

            $this->newLine(2);
            $this->info('=== OVERALL SUMMARY ===');
            $this->displayStats('All Models', $totalStats);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error generating embeddings: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Process a specific model type
     */
    private function processModel(string $modelClass, EmbeddingService $embeddingService, ?string $limit, bool $force): array
    {
        $query = $modelClass::query();

        // Add model-specific filters
        if ($modelClass === Product::class) {
            $query->where('status', true);
        } elseif ($modelClass === Content::class) {
            $query->where('status', true);
        }

        if (!$force) {
            $query->whereNull('embedding');
        }

        if ($limit) {
            $query->limit((int) $limit);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('No items to process.');
            return ['success' => 0, 'failed' => 0, 'total' => 0];
        }

        $this->info("Processing {$items->count()} items...");

        $progressBar = $this->output->createProgressBar($items->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($items as $item) {
            if ($embeddingService->generateEmbedding($item)) {
                $successCount++;
            } else {
                $errorCount++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();

        return [
            'success' => $successCount,
            'failed' => $errorCount,
            'total' => $items->count()
        ];
    }

    /**
     * Display statistics for processed items
     */
    private function displayStats(string $type, array $stats): void
    {
        $this->newLine();
        $this->table(
            [$type . ' - Status', 'Count'],
            [
                ['Success', $stats['success']],
                ['Failed', $stats['failed']],
                ['Total', $stats['total']]
            ]
        );

        if ($stats['total'] > 0) {
            $successRate = round(($stats['success'] / $stats['total']) * 100, 2);
            $this->info("Success rate: {$successRate}%");
        }
    }
}