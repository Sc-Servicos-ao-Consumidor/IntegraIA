<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Product;
use App\Services\PrismService;
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
    public function index()
    {
        $recipes = Recipe::with('products')->latest()->get();
        $products = Product::where('status', true)->orderBy('descricao')->get();

        return Inertia::render('Recipes/Manage', [
            'recipes' => $recipes,
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Legacy fields
            'title' => 'nullable|string|max:255',
            'raw_text' => 'nullable|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            
            // New recipe fields
            'recipe_code' => 'nullable|string|max:255',
            'recipe_name' => 'nullable|string|max:255',
            'cuisine' => 'nullable|string|max:255',
            'recipe_type' => 'nullable|string|max:255',
            'service_order' => 'nullable|string|max:255',
            'preparation_time' => 'nullable|integer|min:1',
            'difficulty_level' => 'nullable|string|in:easy,medium,hard,expert',
            'yield' => 'nullable|string|max:255',
            'channel' => 'nullable|string|max:255',
            
            // Content fields
            'recipe_description' => 'nullable|string',
            'ingredients_description' => 'nullable|string',
            'preparation_method' => 'nullable|string',
            
            // Array fields (stored as JSON)
            'main_ingredients' => 'nullable|array',
            'supporting_ingredients' => 'nullable|array',
            'usage_groups' => 'nullable|array',
            'preparation_techniques' => 'nullable|array',
            'consumption_occasion' => 'nullable|array',
            
            // Reference fields
            'general_images_link' => 'nullable|url',
            'product_code' => 'nullable|string|max:255',
            'content_code' => 'nullable|string|max:255',
            
            // Product associations
            'selected_products' => 'nullable|array',
            'selected_products.*.product_id' => 'required_with:selected_products|exists:products,id',
            'selected_products.*.quantity' => 'nullable|numeric|min:0',
            'selected_products.*.unit' => 'nullable|string|max:50',
            'selected_products.*.ingredient_type' => 'nullable|in:main,supporting',
            'selected_products.*.preparation_notes' => 'nullable|string',
            'selected_products.*.optional' => 'nullable|boolean',
        ]);

        $recipe = Recipe::updateOrCreate(
            ['id' => $request->id],
            collect($data)->except('selected_products')->toArray()
        );

        // Sync products if provided
        if ($request->has('selected_products') && is_array($request->selected_products)) {
            $productData = [];
            foreach ($request->selected_products as $index => $productInfo) {
                $productData[$productInfo['product_id']] = [
                    'quantity' => $productInfo['quantity'] ?? null,
                    'unit' => $productInfo['unit'] ?? null,
                    'ingredient_type' => $productInfo['ingredient_type'] ?? 'main',
                    'preparation_notes' => $productInfo['preparation_notes'] ?? null,
                    'optional' => $productInfo['optional'] ?? false,
                    'order' => $index + 1,
                ];
            }
            $recipe->products()->sync($productData);
        }

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
    }

    public function search(Request $request)
    {
        $query = $request->query('query');

        if (!$query) {
            return response()->json(['error' => 'Missing query.'], 422);
        }

        $prism = new PrismService();
        $response = $prism->getEmbedding($query);

        $queryVector = $response->embeddings[0]->embedding;

        if (!$queryVector) {
            throw new Exception();
        }

        $queryResults = Recipe::query()
            ->nearestNeighbors('embedding', $queryVector, Distance::Cosine)
            ->take(3)
            ->get()
            ->map(function ($recipe) {
                return collect($recipe)->except('embedding');
            });

        return response()->json($queryResults);
    }
    
    public function assistant(Request $request)
    {
        $text = $request->text ?? '';
        $context = $request->context ?? null;

        if (!$text) {
            return response()->json(['error' => 'Missing text.'], 422);
        }

        $prism = new PrismService();
        
        $response = $prism->getResponse($text, json_encode($context));

        return response()->json($response['response'] ?? ['error' => 'No response from AI.'], 200);
    }
}
