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
        try{
            $response = Prism::embeddings()
                ->using(Provider::OpenAI, 'text-embedding-3-small')
                ->fromInput($recipe->raw_text)
                ->asEmbeddings();

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('error');
        }
    }
}
