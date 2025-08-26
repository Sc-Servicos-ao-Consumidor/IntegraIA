<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\PrismService;
use Illuminate\Console\Command;
use NunoMaduro\Collision\Provider;
use Prism\Prism\Prism;

class GenerateRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:generate {count=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate example recipes using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating $count recipes...");

        for ($i = 0; $i < $count; $i++) {
            $prompt = "Create a realistic cooking recipe in this format:\n\nRecipe Name:\nCuisine:\nRecipe Type:\nDifficulty Level:\nYield:\nRecipe Description:\nIngredients Description:\nPreparation Method:";

            $prismService = new PrismService;
            $response = $prismService->getEmbedding($prompt);

            $content = $response['response'] ?? $response;

            $recipeName = $this->extractSection($content, 'Recipe Name');
            $cuisine = $this->extractSection($content, 'Cuisine');
            $recipeType = $this->extractSection($content, 'Recipe Type');
            $difficultyLevel = $this->extractSection($content, 'Difficulty Level');
            $yield = $this->extractSection($content, 'Yield');
            $recipeDescription = $this->extractSection($content, 'Recipe Description');
            $ingredientsDescription = $this->extractSection($content, 'Ingredients Description');
            $preparationMethod = $this->extractSection($content, 'Preparation Method');

            $embeddingInput = "$recipeName\n$cuisine\n$recipeType\n$difficultyLevel\n$yield\n$recipeDescription\n$ingredientsDescription\n$preparationMethod";

            $embeddingResponse = $prismService->getEmbedding($embeddingInput);

            Recipe::create([
                'recipe_name' => trim($recipeName),
                'cuisine' => trim($cuisine),
                'recipe_type' => trim($recipeType),
                'difficulty_level' => trim($difficultyLevel),
                'yield' => trim($yield),
                'recipe_description' => trim($recipeDescription),
                'ingredients_description' => trim($ingredientsDescription),
                'preparation_method' => trim($preparationMethod),
                'embedding' => $embeddingResponse['embeddings'][0]['embedding'] ?? $embeddingResponse,
            ]);

            $this->info("✓ Added: $recipeName");
        }

        $this->info("Done.");
    }

    protected function extractSection(string $text, string $label): string
    {
        preg_match("/$label:\s*(.*?)\n(?:\w+:|$)/s", $text . "\n", $matches);
        return $matches[1] ?? '';
    }
}
