<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recipe;
use App\Services\EmbeddingService;
use App\Services\PrismService;

class GenerateRecipeEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embedding:generate-recipes 
                            {--limit= : Limit the number of recipes to process}
                            {--force : Regenerate embeddings even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for recipes using the AI service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recipe embedding generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        try {
            $embeddingService = new EmbeddingService(new PrismService());

            $query = Recipe::query();

            if (!$force) {
                $query->whereNull('embedding');
            }

            if ($limit) {
                $query->limit((int) $limit);
            }

            $recipes = $query->get();

            if ($recipes->isEmpty()) {
                $this->info('No recipes to process.');
                return Command::SUCCESS;
            }

            $this->info("Processing {$recipes->count()} recipes...");

            $progressBar = $this->output->createProgressBar($recipes->count());
            $progressBar->start();

            $successCount = 0;
            $errorCount = 0;

            foreach ($recipes as $recipe) {
                if ($embeddingService->generateEmbedding($recipe)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $this->warn("\nFailed to generate embedding for recipe ID: {$recipe->id}");
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();

            $this->newLine(2);
            $this->info("Recipe embedding generation completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Success', $successCount],
                    ['Failed', $errorCount],
                    ['Total', $recipes->count()]
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error generating recipe embeddings: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}