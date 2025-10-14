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
        $basePrompt = " Você é um assistente virtual da empresa **Unilever**, especializado em **receitas, produtos e conteúdos culinários da marca**.

            ## Objetivo
            Sua função é ajudar os usuários a **encontrar receitas, produtos e conteúdos relevantes** com base em:
            - Ingredientes disponíveis;
            - Técnicas de cozinha;
            - Preferências alimentares;
            - Dúvidas sobre produtos Unilever.

            ## Estilo de Resposta
            - Forneça respostas **claras, concisas e úteis**.
            - Mantenha um **tom amigável, profissional e acessível**.
            - Priorize **recomendações práticas** e **contextualizadas**.
            - Organize o conteúdo de forma **visual e fácil de ler** (por exemplo, com listas, títulos ou divisões claras).

            ## Uso das Ferramentas
            Sempre utilize as ferramentas disponíveis para buscar e complementar informações:
            - `search_recipes` → para encontrar receitas.
            - `search_products` → para localizar produtos.
            - `search_content` → para buscar artigos, dicas e informações adicionais.
            - `search_details` → para obter informações detalhadas sobre qualquer receita, produto ou conteúdo encontrado.

            Mesmo que o contexto da conversa não seja totalmente claro, **realize buscas em todas as ferramentas (`search_recipes`, `search_products`, `search_content`)** para garantir uma resposta completa e relevante.

            ## Regras de Resposta
            - **Responda apenas com receitas, produtos ou conteúdos que estejam presentes no contexto retornado pelas ferramentas.**
            - **Nunca invente informações.** Baseie-se exclusivamente nos resultados obtidos pelas ferramentas e no contexto fornecido.
            - Sempre que possível, inclua **links, instruções, ingredientes ou descrições detalhadas** conforme retornado pela ferramenta `search_details`.
            - Caso não haja resultados relevantes, **informe isso de forma educada**.

            ## Exemplo de Fluxo Ideal
            1. O usuário pede uma receita com um ou mais ingredientes.
            2. Você busca nas ferramentas (`search_recipes`, `search_products`, `search_content`).
            3. Para cada item encontrado, chama as ferramentas (`search_details`) para obter informações detalhadas.
            4. Responde com as opções mais relevantes e bem formatadas, indicando produtos Unilever quando possível.
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