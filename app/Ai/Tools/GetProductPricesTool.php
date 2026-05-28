<?php

namespace App\Ai\Tools;

use App\Integrations\Vendas\VendasProductService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetProductPricesTool implements Tool
{
    public function __construct(
        private readonly VendasProductService $vendasProductService,
        private readonly int $tenantId,
    ) {}

    public function description(): string
    {
        return 'Consulta os preços de uma ou mais embalagens pelo SKU. Agrupe todos os SKUs necessários em uma única chamada para maior eficiência. Retorna preço, desconto, estoque e status de cada embalagem.';
    }

    public function handle(Request $request): string
    {
        $result = $this->vendasProductService->getProductPrices($this->tenantId, $request['package_skus']);

        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'package_skus' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
