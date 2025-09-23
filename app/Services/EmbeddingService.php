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
            $recipe->recipe_name,
            $recipe->recipe_type,
            $recipe->service_order,
            $recipe->preparation_time,
            $recipe->difficulty_level,
            $recipe->yield,
            $recipe->channel,
            $recipe->recipe_description,
            $recipe->ingredients_description,
            $recipe->preparation_method,
            $recipe->ingredients()->implode(', '),
            $recipe->usage_groups,
            $recipe->preparation_techniques,
            $recipe->consumption_occasion,
            $recipe->cuisines()->implode(', '),
        ]);

        return implode("\n", $parts);
    }

    /**
     * Build embedding text for Product model
     */
    protected function getProductEmbeddingText(Product $product): string
    {
        $parts = array_filter([
            $product->sku,
            $product->marca,
            $product->descricao,
            $product->descricao_breve,
            $product->dicas_utilizacao,
            $product->especificacao_produto,
            $product->perfil_sabor,
            $product->descricao_tabela_nutricional,
            $product->descricao_lista_ingredientes,
            $product->descricao_modos_preparo,
            $product->descricao_rendimentos,
            $product->informacao_adicional,     
            $product->ean,
            $product->status,
        ]);

        return implode("\n", $parts);
    }

    /**
     * Build embedding text for Content model
     */
    protected function getContentEmbeddingText(Content $content): string
    {
        $parts = array_filter([
            $content->nome_conteudo,

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
