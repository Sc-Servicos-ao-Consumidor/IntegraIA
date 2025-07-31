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
            $recipe->title,
            implode(', ', $recipe->tags ?? []),
            collect($recipe->metadata)
                ->map(fn($value, $key) => $key . ': ' . (is_array($value) ? implode(', ', $value) : $value))
                ->implode("\n"),
            $recipe->raw_text,
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
