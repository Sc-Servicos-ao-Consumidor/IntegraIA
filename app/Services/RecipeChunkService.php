<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\RecipeChunk;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class RecipeChunkService
{
    protected PrismService $prismService;

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    /**
     * Generate semantic chunks for a recipe and store chunk embeddings.
     */
    public function generateChunks(Recipe $recipe): int
    {
        $created = 0;

        try {
            // Remove previous chunks to avoid duplicates
            $recipe->chunks()->delete();

            $chunks = $this->buildChunks($recipe);

            foreach ($chunks as $index => $chunk) {
                $embedding = $this->prismService->getEmbedding($chunk['content']);

                RecipeChunk::create([
                    'recipe_id' => $recipe->id,
                    'chunk_type' => $chunk['chunk_type'],
                    'content' => $chunk['content'],
                    'position' => $chunk['position'] ?? $index,
                    'embedding' => new Vector($embedding),
                ]);

                $created++;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate recipe chunks', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    /**
     * Build structured chunks from a recipe.
     */
    protected function buildChunks(Recipe $recipe): array
    {
        $chunks = [];

        // Title & metadata
        $titleParts = array_filter([
            $recipe->recipe_name,
            $recipe->recipe_type,
            $recipe->service_order,
            $recipe->preparation_time,
            $recipe->difficulty_level,
            $recipe->yield,
            $recipe->channel,
        ]);
        if (!empty($titleParts)) {
            $chunks[] = [
                'chunk_type' => 'title_metadata',
                'content' => implode("\n", $titleParts),
                'position' => 0,
            ];
        }

        // Description
        if (!empty($recipe->recipe_description)) {
            $chunks[] = [
                'chunk_type' => 'description',
                'content' => (string) $recipe->recipe_description,
                'position' => 0,
            ];
        }

        // Ingredients (list)
        $ingredientList = trim($recipe->ingredientNamesList());
        if ($ingredientList !== '') {
            $chunks[] = [
                'chunk_type' => 'ingredients',
                'content' => $ingredientList,
                'position' => 0,
            ];
        }
        if (!empty($recipe->ingredients_description)) {
            $chunks[] = [
                'chunk_type' => 'ingredients_description',
                'content' => (string) $recipe->ingredients_description,
                'position' => 0,
            ];
        }

        // Preparation steps (split by newlines or periods)
        if (!empty($recipe->preparation_method)) {
            $raw = str_replace("\r", '', (string) $recipe->preparation_method);
            $byNewline = preg_split('/\n+/', $raw) ?: [];
            $steps = [];
            foreach ($byNewline as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                // Further split long lines by period if multiple sentences
                $sentences = preg_split('/\.(\s+|$)/', $line) ?: [];
                foreach ($sentences as $s) {
                    $s = trim($s);
                    if ($s !== '') {
                        $steps[] = rtrim($s, '.');
                    }
                }
            }

            foreach ($steps as $i => $step) {
                $chunks[] = [
                    'chunk_type' => 'preparation_step',
                    'content' => $step,
                    'position' => $i + 1,
                ];
            }
        }

        // Tags / categories
        $tagParts = array_filter([
            $recipe->cuisineNamesList(),
            $this->implodeJsonArray($recipe->usage_groups),
            $this->implodeJsonArray($recipe->preparation_techniques),
            $this->implodeJsonArray($recipe->consumption_occasion),
        ]);
        if (!empty($tagParts)) {
            $chunks[] = [
                'chunk_type' => 'tags',
                'content' => implode("\n", $tagParts),
                'position' => 0,
            ];
        }

        return $chunks;
    }

    protected function implodeJsonArray($value): string
    {
        if (empty($value)) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return collect($value)->filter()->implode(', ');
        }

        return '';
    }
}


