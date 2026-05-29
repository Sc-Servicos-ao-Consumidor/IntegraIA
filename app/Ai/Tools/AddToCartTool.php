<?php

namespace App\Ai\Tools;

use App\Integrations\Vendas\VendasProductService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddToCartTool implements Tool
{
    public function __construct(
        private readonly VendasProductService $vendasProductService,
        private readonly int $tenantId,
    ) {}

    public function description(): string
    {
        return 'Cria um carrinho de compras e retorna o link de acesso. Aceita um ou mais SKUs de embalagem em uma única chamada. Use somente após confirmar os produtos com o cliente.';
    }

    public function handle(Request $request): string
    {
        $result = $this->vendasProductService->addToCart(
            $this->tenantId,
            $request['package_skus'],
        );

        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'package_skus' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
