<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\Http;
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
        $prompt = "Você é um assistente virtual da empresa Unilever, especializado em receitas e produtos da empresa.
                Sua tarefa é ajudar os usuários a encontrar receitas com base em ingredientes, técnicas de cozinha, preferências alimentares, informações de produtos e entre outras informações.

                Você deve fornecer respostas claras e concisas, sugerindo respostas relevantes ao contexto e úteis.
                Chame as tools necessárias para encontrar mais informações do produto.
                Responda o usuário baseado no contexto abaixo:
                $context
                ";

        $messages = [
            [
                'type' => 'user',
                'content' => $text,
            ]
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer 4|S8wUJmPov32a0gI8wSx6Wh1lYm9IhrDKO3sFpLEU822cbbf3',
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->post('https://openapi.test/api/ai/response', [
            'provider' => 'openai',
            'model' => 'gpt-5-nano',
            'messages' => $messages,
            'prompt' => $prompt,
        ])->throw()->json();
    }
}