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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return Inertia::render('Recipes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'raw_text' => 'required|string|min:30',
            'tags' => 'nullable|array'
        ]);

        Recipe::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        return null;
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        //
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

        return response()->json($response->text);
    }
}
