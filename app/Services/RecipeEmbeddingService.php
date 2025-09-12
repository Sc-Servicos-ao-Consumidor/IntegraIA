<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

class RecipeEmbeddingService
{
    public function generateEmbedding(Recipe $recipe)
    {
        $embeddingInput = collect([
            $recipe->recipe_name,
            $recipe->cuisine,
            $recipe->recipe_type,
            $recipe->recipe_description,
            $recipe->ingredients_description,
            $recipe->preparation_method,
            // todo, change to new ingredients table
            collect($recipe->ingredients ?? [])->implode(', '),
            collect($recipe->usage_groups ?? [])->implode(', '),
            collect($recipe->preparation_techniques ?? [])->implode(', '),
            collect($recipe->consumption_occasion ?? [])->implode(', '),
        ])->filter()->implode("\n");

        try{
            $response = Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-small')
                ->fromInput($embeddingInput)
                ->asEmbeddings();

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('error');
        }
    }
}
