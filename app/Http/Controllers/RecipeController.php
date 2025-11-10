<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateAssistantFeedback;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use App\Models\Ingredient;
use App\Models\Allergen;
use App\Models\Cuisine;
use App\Services\PrismService;
use App\Models\AIAssistantFeedback;
use App\Services\EmbeddingService;
use App\Services\AIToolService;
use App\Models\Tenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
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

        $recipes = Recipe::with(['products', 'contents', 'ingredients', 'cuisines', 'allergens'])
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
        $allergens = Allergen::orderBy('name')->get();
        $cuisines = Cuisine::active()->orderBy('name')->get();

        return Inertia::render('Recipes/Manage', [
            'recipes' => $recipes,
            'products' => $products,
            'contents' => $contents,
            'ingredients' => $ingredients,
            'cuisines' => $cuisines,
            'allergens' => $allergens,
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

            // Allergen associations
            'selected_allergens' => 'nullable|array',
            'selected_allergens.*.allergen_id' => 'nullable|exists:allergens,id',
            'selected_allergens.*.name' => 'nullable|string|max:255',
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
            collect($data)->except(['selected_products', 'selected_contents', 'selected_ingredients', 'selected_cuisines', 'selected_allergens'])->toArray()
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

        // Sync allergens if provided
        if ($request->has('selected_allergens') && is_array($request->selected_allergens)) {
            $allergenIds = [];
            foreach ($request->selected_allergens as $allergenInfo) {
                $allergenId = $allergenInfo['allergen_id'] ?? null;
                $name = $allergenInfo['name'] ?? null;
                if (!$allergenId && $name) {
                    $allergen = Allergen::firstOrCreate(['name' => trim($name)]);
                    $allergenId = $allergen->id;
                }
                if ($allergenId) {
                    $allergenIds[] = $allergenId;
                }
            }
            $recipe->allergens()->sync($allergenIds);
        }

        // Queue embedding and chunk generation to avoid blocking request
        Bus::chain([
            new \App\Jobs\GenerateRecipeEmbedding($recipe->id),
            new \App\Jobs\GenerateRecipeChunks($recipe->id),
        ])->dispatch();

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

    /**
     * Search for allergens by name
     */
    public function searchAllergens(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        $query = $request->query('query');
        $limit = $request->query('limit', 10);

        $allergens = Allergen::where('name', 'ilike', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json($allergens);
    }
}
