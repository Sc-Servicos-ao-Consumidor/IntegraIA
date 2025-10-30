<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recipe;
use App\Services\RecipeChunkService;
use App\Services\PrismService;

class GenerateRecipeChunks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embedding:chunk-recipes 
                            {--limit= : Limit the number of recipes to process}
                            {--force : Regenerate chunks even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate chunked embeddings for recipes (title, description, ingredients, steps, tags)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recipe chunk generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        $service = new RecipeChunkService(new PrismService());

        $query = Recipe::query();

        if (!$force) {
            // Only recipes without chunks
            $query->whereDoesntHave('chunks');
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

        $createdChunks = 0;
        foreach ($recipes as $recipe) {
            $createdChunks += $service->generateChunks($recipe);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Created {$createdChunks} chunks across {$recipes->count()} recipes.");

        return Command::SUCCESS;
    }
}


