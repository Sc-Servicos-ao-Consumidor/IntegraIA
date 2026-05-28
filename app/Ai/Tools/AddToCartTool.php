<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddToCartTool implements Tool
{
    public function __construct(
        private readonly int $tenantId,
        private readonly string $contactId,
    ) {}

    public function description(): string
    {
        return 'Adiciona um produto ao carrinho de compras do cliente. Use essa tool somente após confirmar o produto e a quantidade desejada com o cliente.';
    }

    public function handle(Request $request): string
    {
        // TODO: implementar VendasService::addToCart() quando a API de carrinho estiver disponível
        return json_encode([
            'sucesso' => false,
            'mensagem' => 'API de carrinho ainda não configurada.',
            'sku_package' => $request['sku_package'],
            'quantity' => $request['quantity'],
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku_package' => $schema->string()->required(),
            'quantity' => $schema->integer()->min(1)->required(),
        ];
    }
}
