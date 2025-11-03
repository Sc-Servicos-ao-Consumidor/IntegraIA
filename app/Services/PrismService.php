<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
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
        $this->modelName = config('services.prism_api.chat_model', 'gpt-5-mini');
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
                ->using($this->getProvider(), config('services.prism_api.embedding_model', 'text-embedding-3-large'))
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
    public function getResponse(array $messages, $context = null, array $tools = [], $basePrompt = null): array
    {
        $prompt = $this->buildSystemPrompt($context, $basePrompt);

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
    private function buildSystemPrompt($context = null, $basePrompt = null): string
    {
        $basePrompt = $basePrompt ?? " Você é um assistente virtual da empresa Unilever, especializado em receitas da plataforma Unilever, produtos e conteúdos culinários da marca.


            **Nunca** crie, deduza, invente ou complete informações e/ou receitas que **não existam nas respostas das ferramentas disponíveis**.
            ** É possível que as ferramentas não retornem resultados relevantes para a consulta do usuário, nesse caso, responda de forma educada e fale que não foi possível encontrar resultados relevantes. Não invente informações.**

            ## Objetivo
            Sua função é ajudar os usuários a encontrar receitas, produtos e conteúdos relevantes com base em:
            - Ingredientes disponíveis;
            - Técnicas de cozinha;
            - Preferências alimentares;
            - Dúvidas sobre produtos Unilever.

            ## Estilo de Resposta
            - Forneça respostas claras, concisas e úteis.
            - Mantenha um tom profissional, acessível, amigável e coerente com a identidade da Unilever.
            - Priorize recomendações práticas e diretamente relacionadas à solicitação do usuário.
            - Organize o conteúdo de forma legível, utilizando listas, subtítulos e seções quando apropriado.

            ## Uso das Ferramentas
            Sempre utilize as ferramentas disponíveis para buscar e complementar informações:
            - `search_recipes` → para encontrar receitas.
            - `search_products` → para localizar produtos.
            - `search_content` → para buscar artigos, dicas e informações adicionais.
            - `search_details` → para obter informações detalhadas sobre qualquer receita, produto ou conteúdo encontrado.

            Mesmo que o contexto da conversa não seja totalmente claro, realize buscas em todas as ferramentas (`search_recipes`, `search_products`, `search_content`) para garantir uma resposta completa e relevante.

            ## Regras de Resposta
            - Você **não pode inventar, presumir ou criar informações** que não tenham sido retornadas pelas ferramentas ou explicitamente fornecidas no contexto.
            - Todas as respostas devem se basear exclusivamente em dados retornados pelas ferramentas e no contexto existente.
            - Sempre que possível, inclua detalhes como ingredientes, instruções ou descrições obtidas pela ferramenta `search_details`.
            - Caso não existam resultados relevantes, informe isso de forma educada e ofereça alternativas ou sugestões de busca mais específicas.
            - Não faça suposições nem gere conteúdo genérico; apenas respostas verificáveis e derivadas de fontes confiáveis ou ferramentas ativas.
            - Se a resposta incluir produtos das marcas Knorr ou Arisco, concentre-se em apenas uma das duas marcas na resposta.
            - Dê preferência à Knorr se ambas estiverem presentes.
            - Outros produtos de marcas diferentes podem continuar sendo mencionados normalmente, desde que não concorram diretamente com Knorr ou Arisco.

            ## Fluxo Ideal
            1. O usuário solicita uma receita, produto ou conteúdo.
            2. Você executa buscas nas ferramentas `search_recipes`, `search_products` e `search_content`.
            3. Para cada resultado encontrado, utiliza `search_details` para obter informações detalhadas.
            4. Responde com as opções mais relevantes, bem estruturadas e contextualizadas, destacando produtos Unilever quando aplicável.

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