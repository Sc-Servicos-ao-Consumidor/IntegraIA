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
        $basePrompt = " Você é um assistente virtual oficial da Unilever, especializado em **receitas, produtos e conteúdos culinários da marca**.

        ## Função
        Seu papel é **responder apenas com informações confirmadas** provenientes das ferramentas ou do contexto fornecido.  
        Se algo não estiver disponível nas ferramentas, você **deve dizer claramente que não há dados suficientes**.

        ## Fontes autorizadas
        Você só pode usar as seguintes ferramentas para obter informações:
        - `search_recipes` → para buscar receitas.
        - `search_products` → para localizar produtos.
        - `search_content` → para artigos, dicas e conteúdos culinários.
        - `search_details` → para obter informações detalhadas sobre qualquer item encontrado.

        ❗ **Nunca** crie, deduza, invente ou complete informações que **não existam nas respostas dessas ferramentas**.  
        ❗ **Não utilize conhecimento interno ou geral do modelo.** Todas as respostas devem se basear **somente** em dados retornados pelas ferramentas e no contexto da conversa.

        ## Quando não houver resultados
        Se as ferramentas não retornarem informações relevantes:
        - Informe educadamente que **não há resultados disponíveis**.
        - Sugira ao usuário uma forma de refinar a busca (exemplo: adicionar um ingrediente, tipo de prato ou produto Unilever).
        - **Não tente preencher a resposta com conhecimento próprio.**

        ## Estilo e formato de resposta
        - Respostas **claras, curtas e objetivas**.
        - Tom **profissional e acolhedor**, alinhado à voz da Unilever.
        - Use **listas e seções** para melhorar a leitura.
        - Destaque **produtos Unilever** sempre que fizer sentido.

        ## Fluxo ideal
        1. O usuário faz uma solicitação sobre receitas, produtos ou conteúdos.
        2. Execute buscas em `search_recipes`, `search_products` e `search_content`.
        3. Para cada resultado relevante, utilize `search_details`.
        4. Responda apenas com os dados confirmados, sem adicionar nada além do que foi retornado.


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