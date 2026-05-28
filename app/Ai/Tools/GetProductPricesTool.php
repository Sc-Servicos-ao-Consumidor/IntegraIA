<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetProductPricesTool implements Tool
{
    public function __construct(private readonly int $tenantId) {}

    public function description(): string
    {
        return 'Consulta o preço de um produto pelo código SKU da embalagem. Use essa tool após encontrar o produto para obter o preço atualizado.';
    }

    public function handle(Request $request): string
    {
        // TODO: integrar com a API de preços
        return json_encode([
            'sku_package' => $request['sku_package'],
            'tenant_id' => $this->tenantId,
            'status' => 'unavailable',
            'message' => 'API de preços ainda não configurada.',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku_package' => $schema->string()->required(),
        ];
    }
}
