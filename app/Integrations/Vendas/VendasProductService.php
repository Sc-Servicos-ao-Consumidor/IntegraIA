<?php

namespace App\Integrations\Vendas;

use Illuminate\Http\Client\RequestException;

class VendasProductService
{
    public function __construct(private readonly VendasClient $client) {}

    public function getProductPrices(int $tenantId, array $packageSkus): array
    {
        try {
            $data = $this->client->getProductPrices($tenantId, $packageSkus);

            if (! ($data['sucesso'] ?? false)) {
                return [
                    'sucesso' => false,
                    'mensagem' => $data['mensagem'] ?? 'Erro desconhecido na consulta de preços.',
                ];
            }

            return [
                'sucesso' => true,
                'precos' => $data['dados']['precos'] ?? [],
            ];
        } catch (RequestException $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Falha na conexão com a API Vendas: '.$e->getMessage(),
            ];
        }
    }
}
