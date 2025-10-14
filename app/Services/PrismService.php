<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Illuminate\Support\Facades\Log;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class PrismService
{
    private string $providerName;
    private string $modelName;

    public function __construct()
    {
        $this->providerName = config('services.prism_api.chat_provider', 'openai');
        $this->modelName = config('services.prism_api.chat_model', 'gpt-o4-mini');
    }

    public function buildMessages(array $conversation): array
    {
        return collect($conversation)
            ->map(function ($message) {
                if ($message['type'] === 'user') {
                    return new UserMessage($message['content']);
                } elseif ($message['type'] === 'assistant') {
                    return new AssistantMessage($message['content']);
                }
                // Default fallback for unknown message types
                return new UserMessage($message['content']);
            })
            ->toArray();
    }

    /**
     * Get embedding for given text query
     */
    public function getEmbedding(string $query): array
    {
        try {
            $response = Prism::embeddings()
                ->using($this->getProvider(), config('services.prism_api.embedding_model', 'text-embedding-3-small'))
                ->fromInput($query)
                ->asEmbeddings();

            return $response->embeddings[0]->embedding ?? [];
        } catch (PrismException $e) {
            Log::error('Embedding generation failed:', ['error' => $e->getMessage()]);
            return [];
        } catch (Throwable $e) {
            Log::error('Generic error in embedding:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get AI response with optional context and tools
     */
    public function getResponse(array $messages, $context = null, array $tools = []): array
    {
        $prompt = $this->buildSystemPrompt($context);

        try {
            $response = Prism::text()
                ->using($this->getProvider(), $this->modelName)
                ->withSystemPrompt($prompt)
                ->withMessages($messages)
                ->withTools($tools)
                ->withClientRetry(3, 100)
                ->withMaxSteps(10)
                ->asText();

            if ($response->finishReason->name == 'Error') {
                Log::error('Error: ' . $response->finishReason);
                throw new \Exception('Error: ' . $response->finishReason);
            }

            return [
                'status' => 'success',
                'response' => $response->text,
            ];
        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'response' => 'Text generation failed: ' . $e->getMessage()
            ];
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'response' => 'Generic error: ' . $e->getMessage()
            ];
        }
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
        Use as ferramentas disponíveis para encontrar mais informações quando necessário.
        Sempre utilize as ferramentas search_recipes, search_products e search_content para encontrar informações mesmo que o contexto não seja relevante busque em todas as ferramentas.
        Ao encontrar receitas, produtos e conteúdos, utilize a ferramenta de search_details para obter mais informações sobre o mesmo.
        Responda apenas receitas, produtos e conteúdos que estão no contexto fornecido.
        ";

        if ($context) {
            $basePrompt .= "\n\nContexto relevante:\n" . (is_array($context) ? json_encode($context) : $context);
        }

        return $basePrompt;
    }

    protected function getProvider(): Provider
    {
        return Provider::from($this->providerName);
    }

}