<?php

use App\Ai\Tools\GetProductPricesTool;
use App\Integrations\Vendas\VendasClient;
use App\Integrations\Vendas\VendasProductService;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Tools\Request;

function makePriceRequest(array $packageSkus): Request
{
    return new Request(['package_skus' => $packageSkus]);
}

function priceApiResponse(array $precos = []): array
{
    return [
        'sucesso' => true,
        'mensagem' => 'Consulta de preços realizada com sucesso.',
        'dados' => [
            'empresa_usuario_id' => 1,
            'loja_id' => 1,
            'precos' => $precos,
        ],
    ];
}

function makePriceTool(int $tenantId = 1): GetProductPricesTool
{
    return new GetProductPricesTool(new VendasProductService(new VendasClient), $tenantId);
}

it('returns price data for valid package SKUs', function () {
    Http::fake([
        '*/store/product-price/*' => Http::response(priceApiResponse([
            [
                'sku_embalagem' => '7893500066545',
                'encontrado' => true,
                'preco' => '159.330',
                'preco_br' => '159,33',
                'desconto' => '137.72',
                'desconto_br' => '137,72',
                'estoque' => 0,
                'em_promocao' => false,
                'destacado' => true,
                'ativo' => true,
                'embalagem' => ['id' => 87, 'sku_produto' => '7893500067559', 'nome' => 'Caixa com 24 unidades de 500 g'],
            ],
        ]), 200),
    ]);

    $result = json_decode(makePriceTool()->handle(makePriceRequest(['7893500066545'])), true);

    expect($result['sucesso'])->toBeTrue()
        ->and($result['precos'])->toHaveCount(1)
        ->and($result['precos'][0]['sku_embalagem'])->toBe('7893500066545')
        ->and($result['precos'][0]['preco_br'])->toBe('159,33');
});

it('batches multiple SKUs in a single API call', function () {
    Http::fake([
        '*/store/product-price/*' => Http::response(priceApiResponse([
            ['sku_embalagem' => '7893500066545', 'encontrado' => true, 'preco' => '159.330', 'preco_br' => '159,33', 'desconto' => '137.72', 'desconto_br' => '137,72', 'estoque' => 0, 'em_promocao' => false, 'destacado' => true, 'ativo' => true, 'embalagem' => []],
            ['sku_embalagem' => '7893500020158', 'encontrado' => true, 'preco' => '208.620', 'preco_br' => '208,62', 'desconto' => '202.51', 'desconto_br' => '202,51', 'estoque' => 9999, 'em_promocao' => false, 'destacado' => true, 'ativo' => true, 'embalagem' => []],
        ]), 200),
    ]);

    $result = json_decode(makePriceTool()->handle(makePriceRequest(['7893500066545', '7893500020158'])), true);

    expect($result['sucesso'])->toBeTrue()
        ->and($result['precos'])->toHaveCount(2);

    Http::assertSentCount(1);
});

it('returns error payload on HTTP failure', function () {
    Http::fake([
        '*/store/product-price/*' => Http::response([], 500),
    ]);

    $result = json_decode(makePriceTool()->handle(makePriceRequest(['7893500066545'])), true);

    expect($result['sucesso'])->toBeFalse()
        ->and($result['mensagem'])->toContain('500');
});

it('returns error payload when API responds with sucesso=false', function () {
    Http::fake([
        '*/store/product-price/*' => Http::response([
            'sucesso' => false,
            'mensagem' => 'Token inválido.',
        ], 200),
    ]);

    $result = json_decode(makePriceTool()->handle(makePriceRequest(['7893500066545'])), true);

    expect($result['sucesso'])->toBeFalse()
        ->and($result['mensagem'])->toBe('Token inválido.');
});

it('sends Authorization and tenant headers with the request', function () {
    config()->set('services.vendas.token', 'test-token-123');

    Http::fake([
        '*/store/product-price/*' => Http::response(priceApiResponse(), 200),
    ]);

    makePriceTool()->handle(makePriceRequest(['7893500066545']));

    Http::assertSent(function ($request) {
        $headers = $request->headers();

        return str_contains($request->url(), 'store/product-price/1')
            && isset($headers['Authorization'])
            && $headers['Authorization'][0] === 'Bearer test-token-123'
            && isset($headers['tenant'])
            && $headers['tenant'][0] === '1';
    });
});
