<?php

namespace App\Services\Catalog;

use App\Ai\Agents\CatalogAnswerAgent;

class CatalogAnswerService
{
    public function answerFromContext(string $question, array $ragContext, int $tenantId = 0, string $contactId = ''): array
    {
        if (empty($ragContext)) {
            return [
                'answer' => 'Não encontrei produtos no catálogo correspondentes à sua pergunta.',
                'products' => [],
            ];
        }

        $contextText = json_encode($ragContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $fullPrompt = "Contexto do catálogo:\n{$contextText}\n\nPergunta: {$question}";

        $response = (new CatalogAnswerAgent($tenantId, $contactId))->prompt($fullPrompt);

        $products = array_map(fn (array $product) => [
            'codigo_padrao' => $product['codigo_padrao'] ?? null,
            'product_name' => $product['product_name'] ?? null,
            'brand_name' => $product['brand_name'] ?? null,
            'category_name' => $product['category_name'] ?? null,
            'sub_category_name' => $product['sub_category_name'] ?? null,
            'line_name' => $product['line_name'] ?? null,
            'product_img_url' => $product['product_img_url'] ?? null,
            'packages' => array_map(fn (array $pkg) => [
                'sku_package' => $pkg['sku_package'] ?? null,
                'sku_package_name' => $pkg['sku_package_name'] ?? null,
                'package_description' => $pkg['package_description'] ?? null,
                'gross_weight' => $pkg['gross_weight'] ?? null,
                'net_weight' => $pkg['net_weight'] ?? null,
                'ean' => $pkg['ean'] ?? null,
                'package_img_url' => $pkg['package_img_url'] ?? null,
            ], $product['packages'] ?? []),
        ], $ragContext);

        return [
            'answer' => $response->text,
            'products' => $products,
        ];
    }
}
