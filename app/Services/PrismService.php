<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

use Throwable;

class PrismService
{
    private string $providerName;
    private string $modelName;

    public function __construct()
    {
        $this->providerName = config('services.prism_api.chat_provider', 'openai');
        $this->modelName = config('services.prism_api.chat_model', 'gpt-4');
    }

    /**
     * Build messages array for Prism text generation
     * 
     * @param array $conversation Array of conversation turns with 'type' and 'content'
     * @return array Array of formatted message objects for Prism
     */
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
     * Get AI response with messages array and optional context
     */
    public function getResponse(array $messages, $context = null, array $tools = []): array
    {
        $prompt = $this->buildSystemPrompt($context);

        try {
            $response = Prism::text()
                ->using($this->getProvider(), $this->modelName)
                ->withSystemPrompt($prompt)
                ->withMessages($messages)
                ->withClientRetry(3, 100)
                ->withProviderOptions([
                    'reasoning' => [
                        'effort' => 'low',
                        'max_tokens' => 30000,
                        'exclude' => true,
                        'enabled' => true
                    ],
                ])
                ->withMaxSteps(10)
                ->asText();

            if ($response->finishReason->name == 'Error') {
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
                'message' => 'Text generation failed: ' . $e->getMessage()
            ];
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => 'Generic error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get the provider enum instance.
     *
     * @return Provider
     */
    protected function getProvider(): Provider
    {
        return Provider::from($this->providerName);
    }

    /**
     * Build system prompt for AI assistant
     */
    private function buildSystemPrompt($context = null): string
    {
        $basePrompt = "Você é um assistente virtual da empresa Unilever, especializado em receitas e produtos da empresa.
        Sua tarefa é ajudar os usuários a encontrar receitas com base em ingredientes, técnicas de cozinha, preferências alimentares, informações de produtos e entre outras informações.

        Você deve fornecer respostas claras e concisas, sugerindo respostas relevantes ao contexto e úteis.
        Sempre use as ferramentas disponíveis para buscar e encontrar as informações específicas que o usuário solicita, em vez de depender de contexto prévio.";

        return $basePrompt;
    }

    // Note: Tools functionality not available in current Prism version
    private function tools(): array
    {
        $tools = [];

        $tools[] = Tool::as('weather')
            ->for('Get current weather conditions')
            ->withStringParameter('city', 'The city to get weather for')
            ->using(function (string $city): string {
                return "The weather in {$city} is sunny and 84°F.";
            });

        $tools[] = Tool::as('database_query_product_id')
            ->for('Query the database for product information, using its id')
            ->withNumberParameter('product_id', 'The value of product_id')
            ->using(function (string $product_id): string {

                return "Product ID: {$product_id}, Name: Base de Tomate Knorr, Price: R$19.99";
            });

        return $tools;
    }
}