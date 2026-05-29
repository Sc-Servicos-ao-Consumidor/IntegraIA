<?php

namespace App\Ai\Tools;

use App\Services\Catalog\CatalogSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SearchProductsTool implements Tool
{
    public function __construct(private readonly int $tenantId) {}

    public function description(): string
    {
        return 'Busca produtos no catálogo por nome, marca, subcategoria ou linha. Use essa tool sempre que o cliente perguntar sobre produtos disponíveis. Normalize a query antes de buscar: remova quantidades, abreviações de embalagem (fd, cx, un) e use o nome completo da marca (ex: "Tio João" em vez de "tio joão", "Meu Biju" em vez de "biju").';
    }

    public function handle(Request $request): string
    {
        $results = app(CatalogSearchService::class)->searchAsRagContext(
            query: $request['query'],
            tenantId: $this->tenantId,
        );

        if (empty($results)) {
            return 'Nenhum produto encontrado para a busca: '.$request['query'];
        }

        return json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Nome do produto normalizado, sem quantidades ou abreviações de embalagem. Exemplos: "arroz Tio João 1kg", "feijão preto Meu Biju", "leite de soja Suprasoy 300g".')
                ->required(),
        ];
    }
}
