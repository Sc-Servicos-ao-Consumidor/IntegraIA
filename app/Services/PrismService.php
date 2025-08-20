<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PrismService
{
    private string $apiUrl;
    private string $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('services.prism_api.url', 'https://openapi.test/api/ai');
        $this->apiToken = config('services.prism_api.token');
        
        if (!$this->apiToken) {
            throw new \Exception('Prism API token not configured. Please set PRISM_API_TOKEN in your .env file.');
        }
    }

    /**
     * Get embedding for given text query
     */
    public function getEmbedding(string $query): array
    {
        $response = $this->makeRequest('/embeddings', [
            'provider' => config('services.prism_api.embedding_provider', 'openai'),
            'model' => config('services.prism_api.embedding_model', 'text-embedding-3-small'),
            'text' => $query,
        ]);

        return $response;
    }

    /**
     * Get AI response with optional context and tools
     */
    public function getResponse(string $text, $context = null, array $tools = []): array
    {
        $prompt = $this->buildSystemPrompt($context);

        $requestData = [
            'provider' => config('services.prism_api.chat_provider', 'openai'),
            'model' => config('services.prism_api.chat_model', 'gpt-4'),
            'messages' => [
                [
                    'type' => 'user',
                    'content' => $text,
                ]
            ],
            'prompt' => $prompt,
        ];

        // Add tools if provided
        // if (!empty($tools)) {
        //     $requestData['tools'] = $tools;
        //     $requestData['tool_choice'] = 'auto';
        // }

        return $this->makeRequest('/response', $requestData);
    }

    /**
     * Make HTTP request to Prism API
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->post($this->apiUrl . $endpoint, $data)
        ->json();

        return $response;
    }

    /**
     * Extract embedding array from API response
     */
    public function extractEmbeddingFromResponse(array $response): ?array
    {
        // Handle different response formats
        if (isset($response['embedding'])) {
            return $response['embedding'];
        } elseif (is_array($response) && isset($response[0])) {
            // Handle numbered array format
            return array_values($response);
        } elseif (is_array($response) && count($response) > 0) {
            // Handle object format with numeric keys
            return array_values($response);
        }

        return null;
    }

    /**
     * Build system prompt for AI assistant
     */
    private function buildSystemPrompt($context = null): string
    {
        $basePrompt = "Você é um assistente virtual da empresa Unilever, especializado em receitas e produtos da empresa.
        Sua tarefa é ajudar os usuários a encontrar receitas com base em ingredientes, técnicas de cozinha, preferências alimentares, informações de produtos e entre outras informações.

        Você deve fornecer respostas claras e concisas, sugerindo respostas relevantes ao contexto e úteis.
        Use as ferramentas disponíveis para encontrar mais informações quando necessário.";

        if ($context) {
            $basePrompt .= "\n\nContexto relevante:\n" . (is_array($context) ? json_encode($context) : $context);
        }

        return $basePrompt;
    }
}