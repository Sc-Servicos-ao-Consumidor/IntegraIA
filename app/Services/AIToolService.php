<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentChunk;
use App\Models\Product;
use App\Models\ProductChunk;
use App\Models\Recipe;
use App\Models\RecipeChunk;
use Illuminate\Support\Facades\Http;
use Pgvector\Laravel\Vector;
use Prism\Prism\Facades\Tool;

class AIToolService
{
    protected PrismService $prismService;

    protected EmbeddingService $embeddingService;

    public function __construct(PrismService $prismService, EmbeddingService $embeddingService)
    {
        $this->prismService = $prismService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Get available tools for AI assistant
     */
    public function getTools(?array $allowedNames = null): array
    {
        $tools = [];
        $hasFilter = is_array($allowedNames) && count($allowedNames) > 0;

        $isAllowed = function (string $name) use ($hasFilter, $allowedNames): bool {
            return $hasFilter && in_array($name, $allowedNames, true);
        };

        if ($isAllowed('search_recipes')) {
            $tools[] = Tool::as('search_recipes')
                ->for('Buscar receitas com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar receitas')
                ->withNumberParameter('limit', 'Número máximo de receitas a retornar (padrão: 10)', false)
                ->using(function (string $query, ?int $limit = null) {
                    return $this->searchRecipes($query, $limit ?? 10);
                });
        }

        if ($isAllowed('search_products')) {
            $tools[] = Tool::as('search_products')
                ->for('Buscar produtos com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar produtos')
                ->withNumberParameter('limit', 'Número máximo de produtos a retornar (padrão: 10)', false)
                ->using(function (string $query, ?int $limit = null) {
                    return $this->searchProducts($query, $limit ?? 10);
                });
        }

        if ($isAllowed('search_content')) {
            $tools[] = Tool::as('search_content')
                ->for('Buscar conteúdo com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar conteúdo')
                ->withNumberParameter('limit', 'Número máximo de conteúdos a retornar (padrão: 10)', false)
                ->using(function (string $query, ?int $limit = null) {
                    return $this->searchContent($query, $limit ?? 10);
                });
        }

        if ($isAllowed('get_recipe_details')) {
            $tools[] = Tool::as('get_recipe_details')
                ->for('Obter detalhes completos de uma receita específica')
                ->withNumberParameter('recipeId', 'ID da receita')
                ->using(function (int $recipeId) {
                    return $this->getRecipeDetails($recipeId);
                });
        }

        if ($isAllowed('get_product_details')) {
            $tools[] = Tool::as('get_product_details')
                ->for('Obter detalhes completos de um produto específico')
                ->withNumberParameter('productId', 'ID do produto')
                ->using(function (int $productId) {
                    return $this->getProductDetails($productId);
                });
        }

        if ($isAllowed('get_content_details')) {
            $tools[] = Tool::as('get_content_details')
                ->for('Obter detalhes completos de um conteúdo específico')
                ->withNumberParameter('contentId', 'ID do conteúdo')
                ->using(function (int $contentId) {
                    return $this->getContentDetails($contentId);
                });
        }

        if ($isAllowed('find_recipes_with_product_id')) {
            $tools[] = Tool::as('find_recipes_with_product_id')
                ->for('Encontrar receitas que usam um produto específico')
                ->withNumberParameter('productId', 'ID do produto')
                ->withStringParameter('ingredientType', 'Tipo de ingrediente (main, supporting, ou null para ambos)', false)
                ->using(function (int $productId, ?string $ingredientType = null) {
                    return $this->findRecipesWithProductId($productId, $ingredientType);
                });
        }

        if ($isAllowed('find_content_with_product_id')) {
            $tools[] = Tool::as('find_content_with_product_id')
                ->for('Encontrar conteúdo que usa um produto específico')
                ->withNumberParameter('productId', 'ID do produto')
                ->using(function (int $productId) {
                    return $this->findContentWithProductId($productId);
                });
        }

        if ($isAllowed('find_product_with_recipe_id')) {
            $tools[] = Tool::as('find_product_with_recipe_id')
                ->for('Encontrar produtos que usam uma receita específica')
                ->withNumberParameter('recipeId', 'ID da receita')
                ->using(function (int $recipeId) {
                    return $this->findProductsWithRecipeId($recipeId);
                });
        }

        if ($isAllowed('find_content_with_recipe_id')) {
            $tools[] = Tool::as('find_content_with_recipe_id')
                ->for('Encontrar conteúdo que usa uma receita específica')
                ->withNumberParameter('recipeId', 'ID da receita')
                ->using(function (int $recipeId) {
                    return $this->findContentWithRecipeId($recipeId);
                });
        }

        if ($isAllowed('find_product_with_content_id')) {
            $tools[] = Tool::as('find_product_with_content_id')
                ->for('Encontrar produtos que usam um conteúdo específico')
                ->withNumberParameter('contentId', 'ID do conteúdo')
                ->using(function (int $contentId) {
                    return $this->findProductsWithContentId($contentId);
                });
        }

        if ($isAllowed('find_recipes_with_content_id')) {
            $tools[] = Tool::as('find_recipes_with_content_id')
                ->for('Encontrar receitas que usam um conteúdo específico')
                ->withNumberParameter('contentId', 'ID do conteúdo')
                ->using(function (int $contentId) {
                    return $this->findRecipesWithContentId($contentId);
                });
        }

        return $tools;
    }

    /**
     * Search recipes using semantic search
     */
    protected function searchRecipes(string $query, int $limit = 10): string
    {
        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);

        if (! $embedding || ! is_array($embedding)) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $embeddingVector = new Vector($embedding);

        // Return the top 20 most similar recipe chunks
        $topChunks = RecipeChunk::query()
            ->select(['id', 'recipe_id', 'chunk_type', 'position', 'content'])
            ->selectRaw('embedding <=> ? as distance', [$embeddingVector])
            ->orderBy('distance')
            ->take(20)
            ->get()
            ->map(function ($chunk) {
                return [
                    'id' => $chunk->id,
                    'recipe_id' => $chunk->recipe_id,
                    'chunk_type' => $chunk->chunk_type,
                    'position' => $chunk->position,
                    'content' => $chunk->content,
                    'distance' => isset($chunk->distance) ? (float) $chunk->distance : null,
                ];
            });

        return $topChunks->toJson();
    }

    /**
     * Search products using semantic search
     */
    protected function searchProducts(string $query, int $limit = 10): string
    {
        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);

        if (! $embedding || ! is_array($embedding)) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $embeddingVector = new Vector($embedding);

        // Return the top 20 most similar product chunks
        $topChunks = ProductChunk::query()
            ->select(['id', 'product_id', 'chunk_type', 'position', 'content'])
            ->selectRaw('embedding <=> ? as distance', [$embeddingVector])
            ->orderBy('distance')
            ->take(20)
            ->get()
            ->map(function ($chunk) {
                return [
                    'id' => $chunk->id,
                    'product_id' => $chunk->product_id,
                    'chunk_type' => $chunk->chunk_type,
                    'position' => $chunk->position,
                    'content' => $chunk->content,
                    'distance' => isset($chunk->distance) ? (float) $chunk->distance : null,
                ];
            });

        return $topChunks->toJson();
    }

    /**
     * Search content using semantic search
     */
    protected function searchContent(string $query, int $limit = 10): string
    {
        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);

        if (! $embedding || ! is_array($embedding)) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $embeddingVector = new Vector($embedding);

        // Return the top 20 most similar content chunks
        $topChunks = ContentChunk::query()
            ->select(['id', 'content_id', 'chunk_type', 'position', 'content'])
            ->selectRaw('embedding <=> ? as distance', [$embeddingVector])
            ->orderBy('distance')
            ->take(20)
            ->get()
            ->map(function ($chunk) {
                return [
                    'id' => $chunk->id,
                    'content_id' => $chunk->content_id,
                    'chunk_type' => $chunk->chunk_type,
                    'position' => $chunk->position,
                    'content' => $chunk->content,
                    'distance' => isset($chunk->distance) ? (float) $chunk->distance : null,
                ];
            });

        return $topChunks->toJson();
    }

    /**
     * Get detailed information about a specific recipe
     */
    protected function getRecipeDetails(int $recipeId): string
    {
        $recipe = Recipe::with(['products', 'contents', 'ingredients'])
            ->find($recipeId);

        if (! $recipe) {
            return 'Receita não encontrada';
        }

        return json_encode([
            'id' => $recipe->id,
            'recipe_name' => $recipe->recipe_name,
            'recipe_code' => $recipe->recipe_code,
            'cuisine' => $recipe->cuisine,
            'recipe_type' => $recipe->recipe_type,
            'service_order' => $recipe->service_order,
            'preparation_time' => $recipe->preparation_time,
            'difficulty_level' => $recipe->difficulty_level,
            'yield' => $recipe->yield,
            'channel' => $recipe->channel,
            'recipe_description' => $recipe->recipe_description,
            'ingredients_description' => $recipe->ingredients_description,
            'preparation_method' => $recipe->preparation_method,
            'usage_groups' => $recipe->usage_groups,
            'preparation_techniques' => $recipe->preparation_techniques,
            'consumption_occasion' => $recipe->consumption_occasion,
            'contents_count' => $recipe->contents->count(),
            'products_count' => $recipe->products->count(),
            'ingredients' => $recipe->ingredients,
            'recipe_prompt' => $recipe->recipe_prompt,
        ]);
    }

    /**
     * Get detailed information about a specific product
     */
    protected function getProductDetails(int $productId): string
    {
        $product = Product::with(['groupProduct', 'images', 'recipes', 'contents'])
            ->find($productId);

        if (! $product) {
            return 'Produto não encontrado';
        }

        return json_encode([
            'id' => $product->id,
            'descricao' => $product->descricao,
            'marca' => $product->marca,
            'codigo_padrao' => $product->codigo_padrao,
            'sku' => $product->sku,
            'status' => $product->status,
            'group_product' => $product->groupProduct ? [
                'id' => $product->groupProduct->id,
                'nome' => $product->groupProduct->nome,
                'descricao' => $product->groupProduct->descricao,
            ] : null,
            'images' => $product->images,
            'recipes_count' => $product->recipes->count(),
            'contents_count' => $product->contents->count(),
        ]);
    }

    /**
     * Get detailed information about a specific content
     */
    protected function getContentDetails(int $contentId): string
    {
        $content = Content::find($contentId);

        if (! $content) {
            return 'Conteúdo não encontrado';
        }

        return json_encode([
            'id' => $content->id,
            'nome_conteudo' => $content->nome_conteudo,
            'content_code' => $content->content_code,
            'tipo_conteudo' => $content->tipo_conteudo,
            'pilares' => $content->pilares,
            'canal' => $content->canal,
            'links_conteudo' => $content->links_conteudo,
            'cozinheiro' => $content->cozinheiro,
            'comprador' => $content->comprador,
            'administrador' => $content->administrador,
            'descricao_conteudo' => $content->descricao_conteudo,
            'recipe_count' => $content->recipes()->count(),
            'product_count' => $content->products()->count(),
            'content_prompt' => $content->content_prompt,
        ]);
    }

    /**
     * Find recipes that use a specific product
     */
    protected function findRecipesWithProductId(int $productId, ?string $ingredientType = null): string
    {
        $product = Product::find($productId);

        if (! $product) {
            return 'Produto não encontrado';
        }

        $query = $product->recipes();

        if ($ingredientType) {
            $query->wherePivot('ingredient_type', $ingredientType);
        }

        return $query->get()->map(function ($recipe) {
            return json_encode([
                'id' => $recipe->id,
                'recipe_name' => $recipe->recipe_name,
                'cuisine' => $recipe->cuisine,
                'difficulty_level' => $recipe->difficulty_level,
                'preparation_time' => $recipe->preparation_time,
                'ingredient_type' => $recipe->pivot->ingredient_type,
                'quantity' => $recipe->pivot->quantity,
                'unit' => $recipe->pivot->unit,
                'optional' => $recipe->pivot->optional,
            ]);
        });

    }

    /**
     * Find content that uses a specific product
     */
    protected function findContentWithProductId(int $productId): string
    {
        $content = Content::find($productId);

        if (! $content) {
            return 'Conteúdo não encontrado';
        }

        return $content->map(function ($content) {
            return json_encode([
                'id' => $content->id,
                'nome_conteudo' => $content->nome_conteudo,
            ]);
        });
    }

    /**
     * Find products that use a specific recipe
     */
    protected function findProductsWithRecipeId(int $recipeId): string
    {
        $recipe = Recipe::find($recipeId);

        if (! $recipe) {
            return 'Receita não encontrada';
        }

        return $recipe->products->map(function ($product) {
            return json_encode([
                'id' => $product->id,
                'nome_produto' => $product->descricao,
                'marca' => $product->marca,
            ]);
        });
    }

    /**
     * Find content that uses a specific recipe
     */
    protected function findContentWithRecipeId(int $recipeId): string
    {
        $content = Content::find($recipeId);

        if (! $content) {
            return 'Conteúdo não encontrado';
        }

        return $content->map(function ($content) {
            return json_encode([
                'id' => $content->id,
                'nome_conteudo' => $content->nome_conteudo,
            ]);
        });
    }

    /**
     * Find products that use a specific content
     */
    protected function findProductsWithContentId(int $contentId): string
    {
        $content = Content::find($contentId);

        if (! $content) {
            return 'Conteúdo não encontrado';
        }

        return $content->products->map(function ($product) {
            return json_encode([
                'id' => $product->id,
                'nome_produto' => $product->descricao,
            ]);
        });
    }

    /**
     * Find recipes that use a specific content
     */
    protected function findRecipesWithContentId(int $contentId): string
    {
        $content = Content::find($contentId);

        if (! $content) {
            return 'Conteúdo não encontrado';
        }

        return $content->recipes->map(function ($recipe) {
            return json_encode([
                'id' => $recipe->id,
                'recipe_name' => $recipe->recipe_name,
            ]);
        });
    }

    /**
     * Calls an Botmaker webhook and transfers user to human agent
     */
    protected function transferToHumanAgent(string $userId): string
    {
        $botmakerBaseUrl = config('services.botmaker.base_url');
        $botmakerToken = config('services.botmaker.token');

        $response = Http::post($botmakerBaseUrl, [
            'token' => $botmakerToken,
            'user_id' => $userId,
        ]);

        if ($response->status() !== 200) {
            return 'Falha ao transferir para agente humano: '.$response->body();
        } else {
            return 'Transferencia para agente humano realizada com sucesso';
        }
    }
}
