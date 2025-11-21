<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductChunk;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class ProductChunkService
{
    protected PrismService $prismService;

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    public function generateChunks(Product $product): int
    {
        $created = 0;
        try {
            $product->chunks()->delete();

            $chunks = $this->buildChunks($product);
            foreach ($chunks as $index => $chunk) {
                $embedding = $this->prismService->getEmbedding($chunk['content']);
                ProductChunk::create([
                    'product_id' => $product->id,
                    'chunk_type' => $chunk['chunk_type'],
                    'content' => $chunk['content'],
                    'position' => $chunk['position'] ?? $index,
                    'embedding' => new Vector($embedding),
                ]);
                $created++;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate product chunks', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    protected function buildChunks(Product $product): array
    {
        $chunks = [];

        // Title & metadata
        $title = array_filter([
            $product->descricao,
            $product->sku,
            $product->marca,
            optional($product->groupProduct)->nome,
        ]);
        if (! empty($title)) {
            $chunks[] = [
                'chunk_type' => 'title_metadata',
                'content' => implode("\n", $title),
                'position' => 0,
            ];
        }

        // Descriptive fields
        $map = [
            'especificacao_produto' => 'spec',
            'perfil_sabor' => 'flavor_profile',
            'descricao_tabela_nutricional' => 'nutrition',
            'descricao_lista_ingredientes' => 'ingredients',
            'descricao_modos_preparo' => 'preparation',
            'descricao_rendimentos' => 'yields',
            'informacao_adicional' => 'additional_info',
            'dicas_utilizacao' => 'usage_tips',
            'descricao_breve' => 'short_description',
        ];
        foreach ($map as $field => $chunkType) {
            $value = trim((string) ($product->$field ?? ''));
            if ($value !== '') {
                $chunks[] = [
                    'chunk_type' => $chunkType,
                    'content' => $value,
                    'position' => 0,
                ];
            }
        }

        return $chunks;
    }
}
