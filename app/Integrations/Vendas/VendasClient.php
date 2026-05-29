<?php

namespace App\Integrations\Vendas;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class VendasClient
{
    protected function request(): PendingRequest
    {
        return Http::withToken(config('services.vendas.token'))
            ->baseUrl(config('services.vendas.url'))
            ->acceptJson()
            ->contentType('application/json')
            ->connectTimeout(3)
            ->timeout(30);
    }

    /**
     * @throws RequestException
     */
    public function getProductPrices(int $tenantId, array $packageSkus): array
    {
        return $this->request()
            ->withHeaders(['tenant' => $tenantId])
            ->post("/store/product-price/{$tenantId}", ['package_skus' => $packageSkus])
            ->throw()
            ->json();
    }
}
