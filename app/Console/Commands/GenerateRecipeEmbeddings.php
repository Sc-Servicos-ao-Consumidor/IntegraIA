<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recipe;
use App\Services\RecipeEmbeddingService;
use Pgvector\Laravel\Vector;

class GenerateRecipeEmbeddings extends Command
{
    protected $signature = 'recipes:generate-embeddings';
    protected $description = 'Generate and store embeddings for all recipes without one';

    public function handle(RecipeEmbeddingService $embeddingService): int
    {
        $recipes = Recipe::query()->get();

        if ($recipes->isEmpty()) {
            $this->info('No recipes found that need embeddings.');
            return Command::SUCCESS;
        }

        foreach ($recipes as $recipe) {
            $this->info("Generating embedding for recipe: {$recipe->title}");

            try {
                $embedding = $embeddingService->generateEmbedding($recipe);

                $recipe->embedding = new Vector($embedding);
                $recipe->save();

                $this->info("Saved embedding for: {$recipe->title}");
            } catch (\Exception $e) {
                $this->error("Failed for {$recipe->title}: " . $e->getMessage());
            }
        }

        $this->info('Embeddings generated successfully.');
        return Command::SUCCESS;
    }

}