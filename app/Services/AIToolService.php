<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Distance;

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
    public function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_recipes',
                    'description' => 'Buscar receitas com base em uma consulta de texto usando busca semântica',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Texto da consulta para buscar receitas'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Número máximo de receitas a retornar (padrão: 5)',
                                'default' => 5
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_products',
                    'description' => 'Buscar produtos com base em uma consulta de texto usando busca semântica',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Texto da consulta para buscar produtos'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Número máximo de produtos a retornar (padrão: 5)',
                                'default' => 5
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_content',
                    'description' => 'Buscar conteúdo com base em uma consulta de texto usando busca semântica',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Texto da consulta para buscar conteúdo'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Número máximo de conteúdos a retornar (padrão: 5)',
                                'default' => 5
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_recipe_details',
                    'description' => 'Obter detalhes completos de uma receita específica',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'recipe_id' => [
                                'type' => 'integer',
                                'description' => 'ID da receita'
                            ]
                        ],
                        'required' => ['recipe_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_details',
                    'description' => 'Obter detalhes completos de um produto específico',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'integer',
                                'description' => 'ID do produto'
                            ]
                        ],
                        'required' => ['product_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_recipes_with_product_id',
                    'description' => 'Encontrar receitas que usam um produto específico',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'integer',
                                'description' => 'ID do produto'
                            ],
                            'ingredient_type' => [
                                'type' => 'string',
                                'description' => 'Tipo de ingrediente (main, supporting, ou null para ambos)',
                                'enum' => ['main', 'supporting', null]
                            ]
                        ],
                        'required' => ['product_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_products_with_recipe_id',
                    'description' => 'Encontrar produtos que usam uma receita específica',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'recipe_id' => [
                                'type' => 'integer',
                                'description' => 'ID da receita'
                            ]
                        ],
                        'required' => ['recipe_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'transfer_to_human_agent',
                    'description' => 'Transferir o usuário para um agente humano',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => [
                                'type' => 'string',
                                'description' => 'ID do usuário'
                            ]
                        ],
                        'required' => ['user_id']
                    ]
                ]
            ]
        ];
    }

    /**
     * Execute a tool function
     */
    public function executeTool(string $functionName, array $parameters): array
    {
        try {
            return match ($functionName) {
                'search_recipes' => $this->searchRecipes($parameters),
                'search_products' => $this->searchProducts($parameters),
                'search_content' => $this->searchContent($parameters),
                'get_recipe_details' => $this->getRecipeDetails($parameters),
                'get_product_details' => $this->getProductDetails($parameters),
                'find_recipes_with_product_id' => $this->findRecipesWithProductId($parameters),
                'transfer_to_human_agent' => $this->transferToHumanAgent($parameters),
                default => ['error' => 'Unknown function: ' . $functionName]
            };
        } catch (\Exception $e) { 
            Log::error('Error executing tool function', [
                'function' => $functionName,
                'parameters' => $parameters,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => 'Erro ao executar função: ' . $e->getMessage()];
        }
    }

    /**
     * Search recipes using semantic search
     */
    protected function searchRecipes(array $parameters): array
    {
        $query = $parameters['query'];
        $limit = $parameters['limit'] ?? 5;

        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);
        
        if (!$embedding || !is_array($embedding)) {
            return ['error' => 'Falha ao gerar embedding para a consulta'];
        }

        $recipes = Recipe::query()
            ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
            ->take($limit)
            ->with(['products', 'contents'])
            ->get()
            ->map(function ($recipe) {
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
                ];
            });

        return ['recipes' => $recipes];
    }

    /**
     * Search products using semantic search
     */
    protected function searchProducts(array $parameters): array
    {
        $query = $parameters['query'];
        $limit = $parameters['limit'] ?? 5;

        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);
        
        if (!$embedding || !is_array($embedding)) {
            return ['error' => 'Falha ao gerar embedding para a consulta'];
        }

        $products = Product::query()
            ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
            ->take($limit)
            ->with(['groupProduct', 'detail'])
            ->where('status', true)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'descricao' => $product->descricao,
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
                    'detail' => $product->detail ? [
                        'descricao_produto' => $product->detail->descricao_produto,
                        'ingredientes' => $product->detail->ingredientes,
                        'modo_preparo' => $product->detail->modo_preparo,
                    ] : null,
                ];
            });

        return ['products' => $products];
    }

    /**
     * Search content using semantic search
     */
    protected function searchContent(array $parameters): array
    {
        $query = $parameters['query'];
        $limit = $parameters['limit'] ?? 5;

        $response = $this->prismService->getEmbedding($query);
        $embedding = $this->prismService->extractEmbeddingFromResponse($response);
        
        if (!$embedding || !is_array($embedding)) {
            return ['error' => 'Falha ao gerar embedding para a consulta'];
        }

        $contents = Content::query()
            ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
            ->take($limit)
            ->where('status', true)
            ->get()
            ->map(function ($content) {
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
                ];
            });

        return ['contents' => $contents];
    }

    /**
     * Get detailed information about a specific recipe
     */
    protected function getRecipeDetails(array $parameters): array
    {
        $recipeId = $parameters['recipe_id'];
        
        $recipe = Recipe::with(['products.detail', 'contents'])
            ->find($recipeId);

        if (!$recipe) {
            return ['error' => 'Receita não encontrada'];
        }

        return [
            'recipe' => [
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
                'products' => $recipe->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'descricao' => $product->descricao,
                        'marca' => $product->marca,
                        'quantity' => $product->pivot->quantity,
                        'unit' => $product->pivot->unit,
                        'ingredient_type' => $product->pivot->ingredient_type,
                        'preparation_notes' => $product->pivot->preparation_notes,
                        'optional' => $product->pivot->optional,
                    ];
                }),
                'contents' => $recipe->contents->map(function ($content) {
                    return [
                        'id' => $content->id,
                        'nome_conteudo' => $content->nome_conteudo,
                        'content_code' => $content->content_code,
                    ];
                }),
            ]
        ];
    }

    /**
     * Get detailed information about a specific product
     */
    protected function getProductDetails(array $parameters): array
    {
        $productId = $parameters['product_id'];
        
        $product = Product::with(['groupProduct', 'detail', 'images', 'recipes'])
            ->find($productId);

        if (!$product) {
            return ['error' => 'Produto não encontrado'];
        }

        return [
            'product' => [
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
                'detail' => $product->detail ? [
                    'descricao_produto' => $product->detail->descricao_produto,
                    'ingredientes' => $product->detail->ingredientes,
                    'modo_preparo' => $product->detail->modo_preparo,
                    'informacoes_nutricionais' => $product->detail->informacoes_nutricionais,
                    'informacoes_uso' => $product->detail->informacoes_uso,
                ] : null,
                'recipes_count' => $product->recipes->count(),
                'main_recipes_count' => $product->mainRecipes()->count(),
                'supporting_recipes_count' => $product->supportingRecipes()->count(),
            ]
        ];
    }

    /**
     * Find recipes that use a specific product
     */
    protected function findRecipesWithProductId(array $parameters): array
    {
        $productId = $parameters['product_id'];
        $ingredientType = $parameters['ingredient_type'] ?? null;
        
        $product = Product::find($productId);
        
        if (!$product) {
            return ['error' => 'Produto não encontrado'];
        }

        $query = $product->recipes();
        
        if ($ingredientType) {
            $query->wherePivot('ingredient_type', $ingredientType);
        }

        $recipes = $query->get()->map(function ($recipe) {
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
        });

        return [
            'product' => [
                'id' => $product->id,
                'descricao' => $product->descricao,
                'marca' => $product->marca,
            ],
            'recipes' => $recipes
        ];
    }

    /**
     * Calls an Botmaker webhook and transfers user to human agent
     */
    protected function transferToHumanAgent(array $parameters): array
    {
        $botmakerBaseUrl = config('services.botmaker.base_url');
        $botmakerToken = config('services.botmaker.token');

        $response = Http::post($botmakerBaseUrl, [
            'token' => $botmakerToken,
            'user_id' => $parameters['user_id'],
        ]);

        if ($response->status() !== 200) {
            return ['status' => 'error', 'response' => 'Falha ao transferir para agente humano: ' . $response->body()];
        }
        else {
            return ['status' => 'success', 'response' => 'Transferencia para agente humano realizada com sucesso'];
        }
    }
}
