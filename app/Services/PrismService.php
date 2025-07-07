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
}