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

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    /**
     * Get available tools for AI assistant
     */
    public function getTools(?array $allowedNames = null): array
    {
        $tools = [];
        $hasFilter = is_array($allowedNames) && count($allowedNames) > 0;

        $isAllowed = function (string $name) use ($hasFilter, $allowedNames): bool {
            return ! $hasFilter || in_array($name, $allowedNames, true);
        };

        if ($isAllowed('search_recipes')) {
            $tools[] = Tool::as('search_recipes')
                ->for('Buscar receitas com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar receitas')
                ->withNumberParameter('limit', 'Número máximo de receitas a retornar (padrão: 10)', false)
                ->using(function (string $query, int $limit) {
                    return $this->searchRecipes($query, $limit);
                });
        }

        if ($isAllowed('search_products')) {
            $tools[] = Tool::as('search_products')
                ->for('Buscar produtos com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar produtos')
                ->withNumberParameter('limit', 'Número máximo de produtos a retornar (padrão: 10)', false)
                ->using(function (string $query, int $limit) {
                    return $this->searchProducts($query, $limit);
                });
        }

        if ($isAllowed('search_content')) {
            $tools[] = Tool::as('search_content')
                ->for('Buscar conteúdo com base em uma consulta de texto usando busca semântica')
                ->withStringParameter('query', 'Texto da consulta para buscar conteúdo')
                ->withNumberParameter('limit', 'Número máximo de conteúdos a retornar (padrão: 10)', false)
                ->using(function (string $query, int $limit) {
                    return $this->searchContent($query, $limit);
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
                ->using(function (int $productId, string $ingredientType) {
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
     * Generate embedding vector from input text
     */
    protected function generateEmbeddingVector(string $query): ?Vector
    {
        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);

        if (! $embedding || ! is_array($embedding)) {
            return null;
        }

        return new Vector($embedding);
    }

    /**
     * Find best matching chunks grouped by parent id (e.g., recipe_id, product_id, content_id)
     *
     * @return array{orderedIds: array<int>, matchById: array<int, array{chunk: mixed, distance: float}>}
     */
    protected function findBestChunks(string $chunkModelClass, string $parentIdColumn, Vector $embeddingVector, int $limit): array
    {
        $topChunks = $chunkModelClass::query()
            ->select(['id', $parentIdColumn, 'chunk_type', 'position', 'content'])
            ->selectRaw('embedding <=> ? as distance', [$embeddingVector])
            ->orderBy('distance')
            ->take(max($limit * 5, 20))
            ->get();

        $bestByParent = [];
        foreach ($topChunks as $chunk) {
            $parentId = $chunk->{$parentIdColumn};
            if (! isset($bestByParent[$parentId])) {
                $bestByParent[$parentId] = [
                    'chunk' => $chunk,
                    'distance' => (float) $chunk->distance,
                ];
            }
            if (count($bestByParent) >= $limit) {
                break;
            }
        }

        return [
            'orderedIds' => array_keys($bestByParent),
            'matchById' => $bestByParent,
        ];
    }

    /**
     * Search recipes using semantic search
     */
    protected function searchRecipes(string $query, int $limit = 10): string
    {
        $embeddingVector = $this->generateEmbeddingVector($query);
        if (! $embeddingVector) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $result = $this->findBestChunks(RecipeChunk::class, 'recipe_id', $embeddingVector, $limit);
        $orderedRecipeIds = $result['orderedIds'];
        $bestByRecipe = $result['matchById'];

        $recipes = Recipe::query()
            ->with(['products', 'contents', 'cuisines', 'allergens'])
            ->whereIn('id', $orderedRecipeIds)
            ->get()
            ->sortBy(fn ($r) => array_search($r->id, $orderedRecipeIds))
            ->map(function ($recipe) use ($bestByRecipe) {
                $match = $bestByRecipe[$recipe->id]['chunk'] ?? null;

                return [
                    'id' => $recipe->id,
                    'recipe_name' => $recipe->recipe_name,
                    'recipe_type' => $recipe->recipe_type,
                    'service_order' => $recipe->service_order,
                    'difficulty_level' => $recipe->difficulty_level,
                    'preparation_time' => $recipe->preparation_time,
                    'yield' => $recipe->yield,
                    'channel' => $recipe->channel,
                    'recipe_description' => $recipe->recipe_description,
                    'ingredients_description' => $recipe->ingredients_description,
                    'preparation_method' => $recipe->preparation_method,
                    'usage_groups' => $recipe->usage_groups,
                    'preparation_techniques' => $recipe->preparation_techniques,
                    'consumption_occasion' => $recipe->consumption_occasion,
                    'cuisines' => $recipe->cuisines,
                    'allergens' => $recipe->allergens,
                    'aditional_prompt' => $recipe->recipe_prompt,
                    'match_chunk_type' => $match?->chunk_type,
                    'match_chunk_content' => $match?->content,
                ];
            });

        return $recipes->toJson();

    }

    /**
     * Search products using semantic search
     */
    protected function searchProducts(string $query, int $limit = 10): string
    {
        $embeddingVector = $this->generateEmbeddingVector($query);
        if (! $embeddingVector) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $result = $this->findBestChunks(ProductChunk::class, 'product_id', $embeddingVector, $limit);
        $orderedProductIds = $result['orderedIds'];
        $bestByProduct = $result['matchById'];

        $products = Product::query()
            ->with(['groupProduct'])
            ->where('status', true)
            ->whereIn('id', $orderedProductIds)
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $orderedProductIds))
            ->map(function ($product) use ($bestByProduct) {
                $match = $bestByProduct[$product->id]['chunk'] ?? null;

                return [
                    'id' => $product->id,
                    'nome_produto' => $product->descricao,
                    'codigo_padrao' => $product->codigo_padrao,
                    'sku' => $product->sku,
                    'group_product' => $product->groupProduct?->nome,
                    'marca' => $product->marca,
                    'especificacao_produto' => $product->especificacao_produto,
                    'perfil_sabor' => $product->perfil_sabor,
                    'descricao_tabela_nutricional' => $product->descricao_tabela_nutricional,
                    'descricao_lista_ingredientes' => $product->descricao_lista_ingredientes,
                    'descricao_modos_preparo' => $product->descricao_modos_preparo,
                    'descricao_rendimentos' => $product->descricao_rendimentos,
                    'informacao_adicional' => $product->informacao_adicional,
                    'aditional_prompt' => $product->prompt_uso_informacoes_produto,
                    'match_chunk_type' => $match?->chunk_type,
                    'match_chunk_content' => $match?->content,
                ];
            });

        return $products->toJson();

    }

    /**
     * Search content using semantic search
     */
    protected function searchContent(string $query, int $limit = 10): string
    {
        $embeddingVector = $this->generateEmbeddingVector($query);
        if (! $embeddingVector) {
            return 'Falha ao gerar embedding para a consulta';
        }

        $result = $this->findBestChunks(ContentChunk::class, 'content_id', $embeddingVector, $limit);
        $orderedContentIds = $result['orderedIds'];
        $bestByContent = $result['matchById'];

        $contents = Content::query()
            ->where('status', true)
            ->whereIn('id', $orderedContentIds)
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $orderedContentIds))
            ->map(function ($content) use ($bestByContent) {
                $match = $bestByContent[$content->id]['chunk'] ?? null;

                return [
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
                    'aditional_prompt' => $content->content_prompt,
                    'match_chunk_type' => $match?->chunk_type,
                    'match_chunk_content' => $match?->content,
                ];
            });

        return $contents->toJson();
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
    protected function findRecipesWithProductId(int $productId, string $ingredientType): string
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
            return [
                'id' => $recipe->id,
                'recipe_name' => $recipe->recipe_name,
                'cuisine' => $recipe->cuisine,
                'difficulty_level' => $recipe->difficulty_level,
                'preparation_time' => $recipe->preparation_time,
                'ingredient_type' => $recipe->pivot->ingredient_type,
                'quantity' => $recipe->pivot->quantity,
                'unit' => $recipe->pivot->unit,
                'optional' => $recipe->pivot->optional,
            ];
        })->toJson();

    }

    /**
     * Find content that uses a specific product
     */
    protected function findContentWithProductId(int $productId): string
    {
        $product = Product::with('contents')->find($productId);

        if (! $product) {
            return 'Conteúdo não encontrado';
        }

        return $product->contents->map(function ($content) {
            return [
                'id' => $content->id,
                'nome_conteudo' => $content->nome_conteudo,
            ];
        })->toJson();
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
            return [
                'id' => $product->id,
                'nome_produto' => $product->descricao,
                'marca' => $product->marca,
            ];
        })->toJson();
    }

    /**
     * Find content that uses a specific recipe
     */
    protected function findContentWithRecipeId(int $recipeId): string
    {
        $recipe = Recipe::with('contents')->find($recipeId);

        if (! $recipe) {
            return 'Conteúdo não encontrado';
        }

        return $recipe->contents->map(function ($content) {
            return [
                'id' => $content->id,
                'nome_conteudo' => $content->nome_conteudo,
            ];
        })->toJson();
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
            return [
                'id' => $product->id,
                'nome_produto' => $product->descricao,
            ];
        })->toJson();
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
            return [
                'id' => $recipe->id,
                'recipe_name' => $recipe->recipe_name,
            ];
        })->toJson();
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
