<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
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
        $recipes = Recipe::latest()->get();

        return Inertia::render('Recipes/Manage', [
            'recipes' => $recipes
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
        ]);

        Recipe::updateOrCreate(
            ['id' => $request->id],
            $data
        );

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
