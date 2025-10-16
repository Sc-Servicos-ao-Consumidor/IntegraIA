<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use App\Models\Ingredient;
use App\Models\Cuisine;
use App\Services\PrismService;
use App\Services\EmbeddingService;
use App\Services\AIToolService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Pgvector\Laravel\Distance;
class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search = trim((string) $request->input('search', ''));

        $recipes = Recipe::with(['products', 'contents', 'ingredients', 'cuisines'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('recipe_name', 'ilike', "%{$search}%")
                      ->orWhere('recipe_description', 'ilike', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $products = Product::where('status', true)->orderBy('descricao')->get();
        $contents = Content::where('status', true)->orderBy('nome_conteudo')->get();
        $ingredients = Ingredient::orderBy('name')->get();
        $cuisines = Cuisine::active()->orderBy('name')->get();

        return Inertia::render('Recipes/Manage', [
            'recipes' => $recipes,
            'products' => $products,
            'contents' => $contents,
            'ingredients' => $ingredients,
            'cuisines' => $cuisines,
            'filters' => $request->only(['search', 'per_page'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Recipe fields
            'recipe_code' => 'nullable|string|max:255',
            'recipe_name' => 'string|max:255',
            'recipe_type' => 'string|max:255',
            'service_order' => 'nullable|string|max:255',
            'preparation_time' => 'nullable|integer|min:1',
            'difficulty_level' => 'nullable|string|in:muito_facil,facil,elaborada,muito_elaborada',
            'yield' => 'nullable|string|max:255',
            'channel' => 'nullable',
            'channel.*' => 'string|in:padaria,lanchonete,restaurante,confeitaria',
            
            // Content fields
            'recipe_description' => 'string',
            'ingredients_description' => 'nullable|string',
            'recipe_prompt' => 'nullable|string',
            'preparation_method' => 'string',
            
            // String fields
            'usage_groups' => 'nullable|string|max:255',
            'preparation_techniques' => 'nullable|string|max:255',
            'consumption_occasion' => 'nullable|string|max:255',
            
            // Product associations
            'selected_products' => 'nullable|array',
            'selected_products.*.product_id' => 'required_with:selected_products|exists:products,id',
            'selected_products.*.ingredient_type' => 'nullable|in:main,supporting',
            'selected_products.*.top_dish' => 'nullable|boolean',
            
            // Content associations
            'selected_contents' => 'nullable|array',
            'selected_contents.*.content_id' => 'required_with:selected_contents|exists:contents,id',
            
            // Ingredient associations
            'selected_ingredients' => 'nullable|array',
            'selected_ingredients.*.ingredient_id' => 'nullable|exists:ingredients,id',
            'selected_ingredients.*.ingredient_name' => 'nullable|string|max:255',
            'selected_ingredients.*.primary_ingredient' => 'nullable|boolean',

            // Cuisine associations
            'selected_cuisines' => 'nullable|array',
            'selected_cuisines.*.cuisine_id' => 'nullable|exists:cuisines,id',
            'selected_cuisines.*.name' => 'nullable|string|max:255',
        ]);

        // Normalize channel: accept array and store as CSV string
        if (isset($data['channel'])) {
            if (is_array($data['channel'])) {
                $data['channel'] = implode(',', array_values(array_filter(array_map('strval', $data['channel']))));
            } elseif ($data['channel'] === null) {
                $data['channel'] = null;
            } else {
                $data['channel'] = (string) $data['channel'];
            }
        }

        $recipe = Recipe::updateOrCreate(
            ['id' => $request->id],
            collect($data)->except(['selected_products', 'selected_contents', 'selected_ingredients', 'selected_cuisines'])->toArray()
        );

        // Sync products if provided
        if ($request->has('selected_products') && is_array($request->selected_products)) {
            $productData = [];
            foreach ($request->selected_products as $index => $productInfo) {
                $productData[$productInfo['product_id']] = [
                    'ingredient_type' => $productInfo['ingredient_type'] ?? 'main',
                    'order' => $index + 1,
                    'top_dish' => (bool)($productInfo['top_dish'] ?? false),
                ];
            }
            $recipe->products()->sync($productData);
        }

        // Sync contents if provided
        if ($request->has('selected_contents') && is_array($request->selected_contents)) {
            $contentData = [];
            foreach ($request->selected_contents as $index => $contentInfo) {
                $contentData[$contentInfo['content_id']] = [
                    'order' => $index + 1,
                    'top_dish' => (bool)($contentInfo['top_dish'] ?? false),
                ];
            }
            $recipe->contents()->sync($contentData);
        }

        // Sync ingredients if provided
        if ($request->has('selected_ingredients') && is_array($request->selected_ingredients)) {
            $ingredientData = [];
            foreach ($request->selected_ingredients as $index => $ingredientInfo) {
                $ingredientId = $ingredientInfo['ingredient_id'] ?? null;

                // Determine candidate name: ingredient_name or search_term
                $candidateName = null;
                if (!empty($ingredientInfo['ingredient_name'])) {
                    $candidateName = $ingredientInfo['ingredient_name'];
                } elseif (!empty($ingredientInfo['search_term'])) {
                    $candidateName = $ingredientInfo['search_term'];
                }

                // If no ingredient_id is provided but we have a name, create or find it
                if (!$ingredientId && $candidateName) {
                    $name = trim($candidateName);
                    if ($name !== '') {
                        $ingredient = Ingredient::firstOrCreate(['name' => $name]);
                        $ingredientId = $ingredient->id;
                    }
                }

                if ($ingredientId) {
                    $ingredientData[$ingredientId] = [
                        'primary_ingredient' => (bool)($ingredientInfo['primary_ingredient'] ?? true),
                    ];
                }
            }
            // Use sync to replace current ingredient set with the provided set
            $recipe->ingredients()->sync($ingredientData);
        }

        // Sync cuisines if provided
        if ($request->has('selected_cuisines') && is_array($request->selected_cuisines)) {
            $cuisineIds = [];
            foreach ($request->selected_cuisines as $cuisineInfo) {
                $cuisineId = $cuisineInfo['cuisine_id'] ?? null;
                $name = $cuisineInfo['name'] ?? null;
                if (!$cuisineId && $name) {
                    $cuisine = Cuisine::firstOrCreate(['name' => trim($name)]);
                    $cuisineId = $cuisine->id;
                }
                if ($cuisineId) {
                    $cuisineIds[] = $cuisineId;
                }
            }
            $recipe->cuisines()->sync($cuisineIds);
        }

        $embeddingService = new EmbeddingService(new PrismService());
        $embeddingService->generateEmbedding($recipe);

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
    }

    /**
     * Search for ingredients by name
     */
    public function searchIngredients(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        $query = $request->query('query');
        $limit = $request->query('limit', 10);

        $ingredients = Ingredient::where('name', 'ilike', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json($ingredients);
    }

    /**
     * Search for cuisines by name
     */
    public function searchCuisines(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        $query = $request->query('query');
        $limit = $request->query('limit', 10);

        $cuisines = Cuisine::active()
            ->where('name', 'ilike', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json($cuisines);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'type' => 'nullable|string|in:recipes,products,content,all',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $query = $request->query('query');
        $type = $request->query('type', 'all');
        $limit = $request->query('limit', 2);

        try {
            $prism = new PrismService();
            $response = $prism->getEmbedding($query);

            $embedding = $prism->extractEmbeddingFromResponse($response);

            if (!$embedding || !is_array($embedding)) {
                return response()->json(['error' => 'Failed to generate embedding for query.'], 500);
            }

            $results = [];

            if ($type === 'all' || $type === 'recipes') {
                $recipes = Recipe::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->with(['products'])
                    ->get()
                    ->map(function ($recipe) {
                        return collect($recipe)->except('embedding')->toArray();
                    });
                $results['recipes'] = $recipes;
            }

            if ($type === 'all' || $type === 'products') {
                $products = Product::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->where('status', true)
                    ->with(['groupProduct'])
                    ->get()
                    ->map(function ($product) {
                        return collect($product)->except('embedding')->toArray();
                    });
                $results['products'] = $products;
            }

            if ($type === 'all' || $type === 'content') {
                $contents = Content::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->where('status', true)
                    ->get()
                    ->map(function ($content) {
                        return collect($content)->except('embedding')->toArray();
                    });
                $results['contents'] = $contents;
            }

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    public function assistant(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:1',
            'context' => 'nullable',
            'use_tools' => 'nullable|boolean'
        ]);

        $prism = new PrismService();

        $text = $prism->buildMessages([
            [
                'type' => 'user',
                'content' => $request->text
            ]
        ]);

        $context = $this->cleanResultFromEmbeddings($request->context);
        $useTools = $request->use_tools ?? false;

        try {
            $tools = [];

            // Add tools if requested
            if ($useTools) {
                $aiToolService = new AIToolService($prism, new EmbeddingService($prism));
                $tools = $aiToolService->getTools();
            }

            $response = $prism->getResponse($text, $context, $tools);

            if(isset($response['status']) && $response['status']){
                    return response()->json([
                    'response' => $response['response'] ?? $response
                ], 200);
            }else{
                return response()->json([
                    'response' => $response['response'] ?? 'Erro ao processar a solicitação'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Assistant failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean result data to remove embedding values
     */
    private function cleanResultFromEmbeddings($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                // Skip embedding keys
                if ($key === 'embedding') {
                    continue;
                }
                
                // Recursively clean nested arrays/objects
                if (is_array($value)) {
                    $cleaned[$key] = $this->cleanResultFromEmbeddings($value);
                } else {
                    $cleaned[$key] = $value;
                }
            }
            return $cleaned;
        }
        
        return $data;
    }
}
