<?php

use App\Ai\Agents\CatalogAnswerAgent;
use App\Services\Catalog\CatalogAnswerService;

// ============================================================
// Phase 4.2 — CatalogAnswerService
// ============================================================

function phase4RagContext(): array
{
    return [
        [
            'codigo_padrao' => 'PROD-001',
            'product_name' => 'Arroz Premium',
            'product_description' => 'Arroz branco tipo 1',
            'brand_name' => 'Marca Teste',
            'category_name' => 'Alimentos',
            'sub_category_name' => 'Cereais',
            'line_name' => 'Premium',
            'product_img_url' => 'https://example.com/product.jpg',
            'packages' => [
                [
                    'sku_package' => 'PKG-001',
                    'sku_package_name' => 'Caixa 12 unidades',
                    'package_description' => 'Embalagem com 12 unidades',
                    'gross_weight' => '1.5000',
                    'net_weight' => '1.2000',
                    'ean' => '7891234567890',
                    'package_img_url' => 'https://example.com/pkg.jpg',
                ],
            ],
        ],
    ];
}

it('returns an answer and products array when ragContext is non-empty', function () {
    CatalogAnswerAgent::fake(['O produto Arroz Premium está disponível.']);

    $result = (new CatalogAnswerService)->answerFromContext('Qual produto disponível?', phase4RagContext());

    expect($result)->toHaveKeys(['answer', 'products'])
        ->and($result['answer'])->toBe('O produto Arroz Premium está disponível.')
        ->and($result['products'])->toHaveCount(1);
});

it('returns a safe fallback answer and empty products array when ragContext is empty (not an exception)', function () {
    $result = (new CatalogAnswerService)->answerFromContext('Pergunta qualquer', []);

    expect($result)->toHaveKeys(['answer', 'products'])
        ->and($result['answer'])->toBeString()
        ->and($result['answer'])->not->toBeEmpty()
        ->and($result['products'])->toBeArray()
        ->and($result['products'])->toBeEmpty();
});

it('includes all product fields and nested package fields in the products array', function () {
    CatalogAnswerAgent::fake(['Resposta gerada.']);

    $result = (new CatalogAnswerService)->answerFromContext('Pergunta?', phase4RagContext());

    $product = $result['products'][0];

    expect($product)->toHaveKeys([
        'codigo_padrao', 'product_name', 'brand_name', 'category_name',
        'sub_category_name', 'line_name', 'product_img_url', 'packages',
    ]);

    expect($product['packages'][0])->toHaveKeys([
        'sku_package', 'sku_package_name', 'package_description',
        'gross_weight', 'net_weight', 'ean', 'package_img_url',
    ]);
});

it('never exposes embedding values in the returned array', function () {
    CatalogAnswerAgent::fake(['Resposta sem embeddings.']);

    $result = (new CatalogAnswerService)->answerFromContext('Pergunta?', phase4RagContext());

    foreach ($result['products'] as $product) {
        expect($product)->not->toHaveKey('embedding')
            ->and($product)->not->toHaveKey('searchable_text')
            ->and($product)->not->toHaveKey('embedded_at');

        foreach ($product['packages'] as $pkg) {
            expect($pkg)->not->toHaveKey('embedding');
        }
    }
});

it('calls the AI model with a prompt that contains the ragContext data', function () {
    CatalogAnswerAgent::fake(['Resposta do modelo.']);

    (new CatalogAnswerService)->answerFromContext('Pergunta?', phase4RagContext());

    CatalogAnswerAgent::assertPrompted(fn ($prompt) => $prompt->contains('Arroz Premium'));
});
