<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
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
    public function index()
    {
        $recipes = Recipe::with(['products', 'contents'])->latest()->get();
        $products = Product::where('status', true)->orderBy('descricao')->get();
        $contents = Content::where('status', true)->orderBy('nome_conteudo')->get();

        return Inertia::render('Recipes/Manage', [
            'recipes' => $recipes,
            'products' => $products,
            'contents' => $contents
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
            'recipe_name' => 'nullable|string|max:255',
            'cuisine' => 'nullable|string|max:255',
            'recipe_type' => 'nullable|string|max:255',
            'service_order' => 'nullable|string|max:255',
            'preparation_time' => 'nullable|integer|min:1',
            'difficulty_level' => 'nullable|string|in:facil,medio,dificil,expert',
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
            'selected_products.*.ingredient_type' => 'nullable|in:main,supporting',
            
            // Content associations
            'selected_contents' => 'nullable|array',
            'selected_contents.*.content_id' => 'required_with:selected_contents|exists:contents,id',
            'selected_contents.*.top_dish' => 'nullable|in:sim,nao',
        ]);

        $recipe = Recipe::updateOrCreate(
            ['id' => $request->id],
            collect($data)->except(['selected_products', 'selected_contents'])->toArray()
        );

        // Sync products if provided
        if ($request->has('selected_products') && is_array($request->selected_products)) {
            $productData = [];
            foreach ($request->selected_products as $index => $productInfo) {
                $productData[$productInfo['product_id']] = [
                    'ingredient_type' => $productInfo['ingredient_type'] ?? 'main',
                    'order' => $index + 1,
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
                    'top_dish' => $contentInfo['top_dish'] ?? 'nao',
                ];
            }
            $recipe->contents()->sync($contentData);
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
                    ->with(['groupProduct', 'detail'])
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
            // 'use_tools' => 'nullable|boolean'
        ]);
        $prism = new PrismService();

        $text = $prism->buildMessages([
            [
                'type' => 'user',
                'content' => $request->text
            ]
        ]);

        $context = $this->cleanResultFromEmbeddings($request->context);
        // $useTools = $request->use_tools ?? false;

        try {
            // $tools = [];

            // Add tools if requested
            // if ($useTools) {
            //     $aiToolService = new AIToolService($prism, new EmbeddingService($prism));
            //     $tools = $aiToolService->getTools();
            // }

            $response = $prism->getResponse($text, $context);

            // // Handle tool calls if present
            // if (isset($response['tool_calls']) && !empty($response['tool_calls'])) {
            //     $aiToolService = new AIToolService($prism, new EmbeddingService($prism));
            //     $toolResults = [];

            //     foreach ($response['tool_calls'] as $toolCall) {
            //         $functionName = $toolCall['function']['name'] ?? '';
            //         $parameters = $toolCall['function']['arguments'] ?? [];
                    
            //         if (is_string($parameters)) {
            //             $parameters = json_decode($parameters, true) ?? [];
            //         }

            //         $result = $aiToolService->executeTool($functionName, $parameters);
                    
            //         // Clean result to remove any embedding data
            //         $cleanResult = $this->cleanResultFromEmbeddings($result);
                    
            //         $toolResults[] = [
            //             'tool_call_id' => $toolCall['id'] ?? uniqid(),
            //             'function_name' => $functionName,
            //             'result' => $cleanResult
            //         ];
            //     }

            //     // Send tool results back to AI for final response
            //     $finalResponse = $prism->getResponse(
            //         "Com base nos resultados das ferramentas, forneça uma resposta completa ao usuário.",
            //         [
            //             'original_query' => $text,
            //             'tool_results' => $toolResults,
            //             'context' => $context
            //         ]
            //     );

            //     return response()->json([
            //         'response' => $finalResponse['response'] ?? $finalResponse,
            //         'tool_calls' => $toolResults
            //     ], 200);
            // }

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
