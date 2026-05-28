<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetProductPricesTool implements Tool
{
    public function __construct(private readonly int $tenantId) {}

    public function description(): string
    {
        return 'Consulta os preços de uma ou mais embalagens pelo SKU. Agrupe todos os SKUs necessários em uma única chamada para maior eficiência. Retorna preço, desconto, estoque e status de cada embalagem.';
    }

    public function handle(Request $request): string
    {
        $packageSkus = $request['package_skus'];


        try {
            $response = Http::withToken(config('services.store_api.token'))
                ->withHeaders(['tenant' => $this->tenantId])
                ->post(
                    config('services.store_api.url').'/store/product-price/'.$this->tenantId,
                    ['package_skus' => $packageSkus]
                );

                //dd($response->body());

            if ($response->failed()) {
                return json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Erro ao consultar preços: HTTP '.$response->status(),
                ], JSON_UNESCAPED_UNICODE);
            }

            $data = $response->json();

            if (! ($data['sucesso'] ?? false)) {
                return json_encode([
                    'sucesso' => false,
                    'mensagem' => $data['mensagem'] ?? 'Erro desconhecido na consulta de preços.',
                ], JSON_UNESCAPED_UNICODE);
            }

            return json_encode([
                'sucesso' => true,
                'precos' => $data['dados']['precos'] ?? [],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (RequestException $e) {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'Falha na conexão com a API de preços: '.$e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'package_skus' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
