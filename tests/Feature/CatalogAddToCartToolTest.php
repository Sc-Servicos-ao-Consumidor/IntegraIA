<?php

use App\Ai\Tools\AddToCartTool;
use App\Integrations\Vendas\VendasClient;
use App\Integrations\Vendas\VendasProductService;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Tools\Request;

function makeCartRequest(array $packageSkus): Request
{
    return new Request(['package_skus' => $packageSkus]);
}

function cartApiResponse(array $dados = []): array
{
    return [
        'sucesso' => true,
        'mensagem' => 'Carrinho criado e link gerado com sucesso.',
        'dados' => array_merge([
            'empresa_usuario_id' => 1,
            'loja_id' => 1,
            'carrinho_id' => 1227,
            'link_carrinho' => 'https://example.com/carrinho-gerado/1227',
            'link_expira_em' => '2026-06-04 23:30:32',
            'itens' => [],
        ], $dados),
    ];
}

function makeCartTool(int $tenantId = 1): AddToCartTool
{
    return new AddToCartTool(new VendasProductService(new VendasClient), $tenantId);
}

it('returns cart data with link on success', function () {
    Http::fake([
        '*/store/cart-link/*' => Http::response(cartApiResponse([
            'itens' => [
                ['embalagem_id' => 87, 'sku_embalagem' => '7893500066545', 'quantidade' => 1, 'preco_unitario' => '159.33', 'preco_total' => '159.33'],
                ['embalagem_id' => 2, 'sku_embalagem' => '7893500020158', 'quantidade' => 1, 'preco_unitario' => '208.62', 'preco_total' => '208.62'],
            ],
        ]), 200),
    ]);

    $result = json_decode(makeCartTool()->handle(makeCartRequest(['7893500066545', '7893500020158'])), true);

    expect($result['sucesso'])->toBeTrue()
        ->and($result['dados']['carrinho_id'])->toBe(1227)
        ->and($result['dados']['link_carrinho'])->toContain('carrinho-gerado')
        ->and($result['dados']['itens'])->toHaveCount(2);
});

it('returns error payload on HTTP failure', function () {
    Http::fake([
        '*/store/cart-link/*' => Http::response([], 500),
    ]);

    $result = json_decode(makeCartTool()->handle(makeCartRequest(['7893500066545'])), true);

    expect($result['sucesso'])->toBeFalse()
        ->and($result['mensagem'])->toContain('500');
});

it('returns error payload when API responds with sucesso=false', function () {
    Http::fake([
        '*/store/cart-link/*' => Http::response([
            'sucesso' => false,
            'mensagem' => 'Token inválido.',
        ], 200),
    ]);

    $result = json_decode(makeCartTool()->handle(makeCartRequest(['7893500066545'])), true);

    expect($result['sucesso'])->toBeFalse()
        ->and($result['mensagem'])->toBe('Token inválido.');
});

it('sends loja_id, package_skus and tenant header to the correct endpoint', function () {
    config()->set('services.vendas.token', 'test-token-123');

    Http::fake([
        '*/store/cart-link/*' => Http::response(cartApiResponse(), 200),
    ]);

    makeCartTool(5)->handle(makeCartRequest(['7893500066545', '7893500020158']));

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), 'store/cart-link/5')
            && $body['loja_id'] === 5
            && $body['package_skus'] === ['7893500066545', '7893500020158']
            && $request->hasHeader('Authorization', 'Bearer test-token-123')
            && $request->hasHeader('tenant', '5');
    });
});
