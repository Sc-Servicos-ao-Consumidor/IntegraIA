<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPackaging;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductImportService
{
    public function importAll(string $baseUrl, array $headers = []): void
    {
        $currentUrl = $baseUrl;
        
        do {
            $response = Http::withHeaders($headers)->get($currentUrl);
            if (!$response->successful()) {
                Log::error('Product import failed',[ 'status' => $response->status(), 'body' => $response->body()]);
                break;
            }

            $payload = $response->json();
            $items = $payload['data'] ?? $payload['items'] ?? $payload['results'] ?? [];
            foreach ($items as $item) {
                $this->upsertProductWithPackagings($item);
            }

            // Use next_cursor for pagination
            $nextCursor = $payload['next_cursor'] ?? null;
            if ($nextCursor) {
                $separator = str_contains($baseUrl, '?') ? '&' : '?';
                $currentUrl = $baseUrl . $separator . 'cursor=' . urlencode($nextCursor);
                Log::info('Current URL: ' . $currentUrl);
            } else {
                $currentUrl = null;
            }
        } while ($currentUrl);
    }

    protected function upsertProductWithPackagings(array $item): void
    {
        $productData = [
            'ulid' => $item['ulid'] ?? Str::ulid(),
            'slug' => $item['slug'] ?? Str::slug($item['descricao'] ?? ''),
            'codigo_padrao' => $item['codigo_padrao'] ?? null,
            'sku' => $item['sku'] ?? null,
            'descricao' => $item['descricao'] ?? null,
            'descricao_breve' => $item['descricao_breve'] ?? null,
            'especificacao_produto' => $item['especificacao_produto'] ?? null,
            'ean' => $item['ean'] ?? null,
            'localizacao' => $item['localizacao'] ?? null,
            'nome_responsavel' => $item['nome_responsavel'] ?? null,
            'telefone' => $item['telefone'] ?? null,
            'whatsapp' => $item['whatsapp'] ?? null,
            'site' => $item['site'] ?? null,
            'status' => (bool)($item['status'] ?? true),
            'produto_familia_id' => $item['produto_familia_id'] ?? null,
            'produto_grupo_id' => $item['produto_grupo_id'] ?? null,
            'produto_linha_id' => $item['produto_linha_id'] ?? null,
            'produto_sub_linha_id' => $item['produto_sub_linha_id'] ?? null,
        ];

        // Prefer matching by ULID or slug or descricao
        $product = Product::query()
            ->when(!empty($item['ulid']), fn($q) => $q->orWhere('ulid', $item['ulid']))
            ->when(!empty($item['slug']), fn($q) => $q->orWhere('slug', $item['slug']))
            ->when(!empty($item['descricao']), fn($q) => $q->orWhere('descricao', $item['descricao']))
            ->first();

        if ($product) {
            $product->update($productData);
        } else {
            $product = Product::create($productData);
        }

        // Upsert packagings
        $packagings = $item['produto_embalagens'] ?? [];
        foreach ($packagings as $pack) {
            $packData = [
                'product_id' => $product->id,
                'ulid' => $pack['ulid'] ?? null,
                'codigo_padrao' => $pack['codigo_padrao'] ?? null,
                'sku' => $pack['sku'] ?? null,
                'ean' => $pack['ean'] ?? null,
                'descricao' => $pack['descricao'] ?? null,
                'quantidade_caixa' => $pack['quantidade_caixa'] ?? null,
                'embalagem_tipo' => $pack['embalagem_tipo'] ?? null,
                'peso_liquido' => $pack['peso_liquido'] ?? null,
                'peso_bruto' => $pack['peso_bruto'] ?? null,
                'validade' => $pack['validade'] ?? null,
                'descontinuado' => (bool)($pack['descontinuado'] ?? false),
                'status' => (bool)($pack['status'] ?? true),
            ];

            // Match existing packaging by ULID or SKU to avoid duplicates
            $existing = ProductPackaging::where('product_id', $product->id)
                ->when(!empty($pack['ulid']), fn($q) => $q->orWhere('ulid', $pack['ulid']))
                ->when(!empty($pack['sku']), fn($q) => $q->orWhere('sku', $pack['sku']))
                ->first();

            if ($existing) {
                $existing->update($packData);
            } else {
                ProductPackaging::create($packData);
            }
        }
    }
}


