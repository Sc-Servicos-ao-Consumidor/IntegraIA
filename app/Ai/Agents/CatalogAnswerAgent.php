<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AddToCartTool;
use App\Ai\Tools\GetProductPricesTool;
use App\Ai\Tools\SearchProductsTool;
use App\Integrations\Vendas\VendasProductService;
use App\Models\CatalogRequest;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-4.1-mini')]
#[Temperature(0.5)]
#[Timeout(120)]
#[MaxSteps(10)]
class CatalogAnswerAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    private const HISTORY_LIMIT = 7;

    public function __construct(
        private readonly VendasProductService $vendasProductService,
        private readonly int $tenantId,
        private readonly string $contactId,
        private readonly string $sessionId,
        private readonly int $catalogRequestId,
    ) {}

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        Você é um assistente de vendas especializado em catálogo de produtos.

        Suas responsabilidades:
        - Buscar produtos no catálogo quando o cliente perguntar sobre algum item.
        - Consultar preços de todas as embalagens encontradas em uma única chamada (agrupe os SKUs).
        - Adicionar produtos ao carrinho quando o cliente solicitar.

        Regras importantes:
        - Sempre use a tool de busca antes de responder perguntas sobre produtos.
        - Após encontrar produtos, consulte os preços de todas as embalagens relevantes de uma só vez.
        - Nunca invente produtos ou preços que não vieram das tools.
        - Se um produto não for encontrado na busca, informe ao cliente claramente.
        - Apresente os produtos com nome, marca, embalagens disponíveis, preço e estoque.
        - Use o campo "preco_br" para exibir preços no formato brasileiro (ex: R$ 159,33).
        - Se "estoque" for 0, informe que o produto está indisponível no momento.
        - Só adicione ao carrinho após confirmação explícita do cliente com o SKU e a quantidade.
        INSTRUCTIONS;
    }

    public function messages(): iterable
    {
        $messages = [];

        CatalogRequest::where('session_id', $this->sessionId)
            ->where('id', '!=', $this->catalogRequestId)
            ->whereNotNull('ai_answer')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->reverse()
            ->each(function (CatalogRequest $previous) use (&$messages) {
                $answer = is_array($previous->ai_answer)
                    ? json_encode($previous->ai_answer, JSON_UNESCAPED_UNICODE)
                    : $previous->ai_answer;

                $messages[] = new Message('user', $previous->question);
                $messages[] = new Message('assistant', (string) $answer);
            });

        return array_slice($messages, -self::HISTORY_LIMIT);
    }

    public function tools(): iterable
    {
        return [
            new SearchProductsTool($this->tenantId),
            new GetProductPricesTool($this->vendasProductService, $this->tenantId),
            new AddToCartTool($this->tenantId, $this->contactId),
        ];
    }
}
