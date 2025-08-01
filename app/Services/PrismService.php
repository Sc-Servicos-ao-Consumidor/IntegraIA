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

    public function getResponse(string $text, $context=null){
            return Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt("Você é um assistente virtual especializado em receitas de culinária Unilever.
                Sua tarefa é ajudar os usuários a encontrar receitas com base em ingredientes, técnicas de cozinha e preferências alimentares.

                Você deve fornecer respostas claras e concisas, sugerindo receitas relevantes e úteis.
                Responda o usuário baseado no contexto abaixo:
                $context
                ")

                ->withPrompt($text)
                ->asText();
    }
}