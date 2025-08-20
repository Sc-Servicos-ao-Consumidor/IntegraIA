<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected PrismService $prismService;

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    /**
     * Generate and store embedding for a model
     */
    public function generateEmbedding(Model $model): bool
    {
        try {
            $embeddingText = $this->getEmbeddingText($model);
            
            if (!$embeddingText) {
                Log::warning("No embedding text generated for model", [
                    'model_type' => get_class($model),
                    'model_id' => $model->id
                ]);
                return false;
            }

            $response = $this->prismService->getEmbedding($embeddingText);
            
            $embedding = $this->prismService->extractEmbeddingFromResponse($response);
            
            if (!$embedding || !is_array($embedding)) {
                Log::error("No valid embedding returned from API", [
                    'response_keys' => is_array($response) ? array_keys($response) : 'not_array',
                    'response_count' => is_array($response) ? count($response) : 0,
                    'model_type' => get_class($model),
                    'model_id' => $model->id
                ]);
                return false;
            }

            $model->embedding = $embedding;
            $model->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Error generating embedding', [
                'error' => $e->getMessage(),
                'model_type' => get_class($model),
                'model_id' => $model->id
            ]);
            return false;
        }
    }

    /**
     * Generate embeddings for multiple models
     */
    public function generateBatchEmbeddings(array $models): array
    {
        $results = [];
        
        foreach ($models as $model) {
            $results[$model->id] = $this->generateEmbedding($model);
        }
        
        return $results;
    }

    /**
     * Get embedding text for different model types
     */
    protected function getEmbeddingText(Model $model): string
    {
        return match (get_class($model)) {
            Recipe::class => $this->getRecipeEmbeddingText($model),
            Product::class => $this->getProductEmbeddingText($model),
            Content::class => $this->getContentEmbeddingText($model),
            default => throw new \InvalidArgumentException('Unsupported model type for embedding generation')
        };
    }

    /**
     * Build embedding text for Recipe model
     */
    protected function getRecipeEmbeddingText(Recipe $recipe): string
    {
        $parts = array_filter([
            $recipe->title,
            $recipe->recipe_name,
            $recipe->recipe_description,
            $recipe->ingredients_description,
            $recipe->preparation_method,
            $recipe->cuisine,
            $recipe->recipe_type,
            $recipe->channel,
            $recipe->difficulty_level,
            implode(', ', $recipe->tags ?? []),
            implode(', ', $recipe->main_ingredients ?? []),
            implode(', ', $recipe->supporting_ingredients ?? []),
            implode(', ', $recipe->usage_groups ?? []),
            implode(', ', $recipe->preparation_techniques ?? []),
            implode(', ', $recipe->consumption_occasion ?? []),
            $recipe->raw_text,
        ]);

        // Include metadata if present
        if ($recipe->metadata) {
            $metadataText = collect($recipe->metadata)
                ->map(fn($value, $key) => $key . ': ' . (is_array($value) ? implode(', ', $value) : $value))
                ->implode("\n");
            $parts[] = $metadataText;
        }

        return implode("\n", $parts);
    }

    /**
     * Build embedding text for Product model
     */
    protected function getProductEmbeddingText(Product $product): string
    {
        $parts = array_filter([
            $product->descricao,
            $product->marca,
            $product->codigo_padrao,
            $product->sku,
        ]);

        // Include group product information if available
        if ($product->groupProduct) {
            $parts[] = $product->groupProduct->nome ?? '';
            $parts[] = $product->groupProduct->descricao ?? '';
        }

        // Include product details if available
        if ($product->detail) {
            $parts[] = $product->detail->descricao_produto ?? '';
            $parts[] = $product->detail->ingredientes ?? '';
            $parts[] = $product->detail->modo_preparo ?? '';
            $parts[] = $product->detail->informacoes_nutricionais ?? '';
            $parts[] = $product->detail->informacoes_uso ?? '';
        }

        return implode("\n", $parts);
    }

    /**
     * Build embedding text for Content model
     */
    protected function getContentEmbeddingText(Content $content): string
    {
        $parts = array_filter([
            $content->nome_conteudo,
            $content->descricao_tabela_nutricional,
            $content->descricao_lista_ingredientes,
            $content->descricao_modos_preparo,
            $content->descricao_rendimentos,
            $content->tipo_conteudo,
            $content->pilares,
            $content->canal,
            $content->descricao_conteudo,
        ], function($value) {
            return !empty($value) && is_string($value);
        });

        // Include links if present
        if ($content->links_conteudo && is_array($content->links_conteudo)) {
            $linkStrings = array_filter($content->links_conteudo, function($link) {
                return is_string($link) && !empty($link);
            });
            if (!empty($linkStrings)) {
                $parts[] = implode(' ', $linkStrings);
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Regenerate embeddings for all records of a model type
     */
    public function regenerateAllEmbeddings(string $modelClass): array
    {
        if (!in_array($modelClass, [Recipe::class, Product::class, Content::class])) {
            throw new \InvalidArgumentException('Unsupported model class');
        }

        $models = $modelClass::all();
        $results = [
            'total' => $models->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($models as $model) {
            if ($this->generateEmbedding($model)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'id' => $model->id,
                    'type' => get_class($model)
                ];
            }
        }

        return $results;
    }
}
