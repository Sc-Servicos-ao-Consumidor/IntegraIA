<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

class PrismService
{
    public function getEmbedding(string $query) {
        return Prism::embeddings()
            ->using(Provider::OpenAI, 'text-embedding-3-small')
            ->fromInput($query)
            ->asEmbeddings();
    }

    public function getResponse(string $text){
            return Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt("You are a helpful chef assistant.")
                ->withPrompt($text)
                ->asText();
    }
}