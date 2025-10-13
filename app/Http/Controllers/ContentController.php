<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search = trim((string) $request->input('search', ''));

        $contents = Content::with(['recipes', 'products'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome_conteudo', 'ilike', "%{$search}%")
                      ->orWhere('descricao_conteudo', 'ilike', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $recipes = Recipe::whereNotNull('recipe_name')->orderBy('recipe_name')->get();
        $products = Product::where('status', true)->orderBy('descricao')->get();

        return Inertia::render('Contents/Manage', [
            'contents' => $contents,
            'recipes' => $recipes,
            'products' => $products,
            'filters' => $request->only(['search', 'per_page'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome_conteudo' => 'required|string|max:255',
            'content_code' => 'nullable|string|max:255|unique:contents,content_code,' . $request->id,
            'tipo_conteudo' => 'required|string|max:255',
            'pilares' => 'nullable|string|max:255',
            'canal' => 'nullable',
            'canal.*' => 'string|in:padaria,lanchonete,restaurante,confeitaria',
            'links_conteudo' => 'nullable|array',
            'cozinheiro' => 'required|boolean',
            'comprador' => 'required|boolean',
            'administrador' => 'required|boolean',
            'status' => 'nullable|boolean',
            'descricao_conteudo' => 'required|string',
            'content_prompt' => 'nullable|string',
            // Recipe associations
            'selected_recipes' => 'nullable|array',
            'selected_recipes.*.recipe_id' => 'required_with:selected_recipes|exists:recipes,id',
            'selected_recipes.*.top_dish' => 'nullable|boolean',
            // Product associations
            'selected_products' => 'nullable|array',
            'selected_products.*.product_id' => 'required_with:selected_products|exists:products,id',
            'selected_products.*.featured' => 'nullable|in:sim,nao',
            'selected_products.*.notes' => 'nullable|string',
        ]);

        // Normalize canal: accept array and store as CSV string
        if (isset($data['canal'])) {
            if (is_array($data['canal'])) {
                $data['canal'] = implode(',', array_values(array_filter(array_map('strval', $data['canal']))));
            } elseif ($data['canal'] === null) {
                $data['canal'] = null;
            } else {
                $data['canal'] = (string) $data['canal'];
            }
        }

        $content = Content::updateOrCreate(
            ['id' => $request->id],
            collect($data)->except(['selected_recipes', 'selected_products'])->toArray()
        );

        // Sync recipes if provided
        if ($request->has('selected_recipes') && is_array($request->selected_recipes)) {
            $recipeData = [];
            foreach ($request->selected_recipes as $index => $recipeInfo) {
                $recipeData[$recipeInfo['recipe_id']] = [
                    'order' => $index + 1,
                    'top_dish' => (bool)($recipeInfo['top_dish'] ?? false),
                ];
            }
            $content->recipes()->sync($recipeData);
        }

        // Sync products if provided
        if ($request->has('selected_products') && is_array($request->selected_products)) {
            $productData = [];
            foreach ($request->selected_products as $index => $productInfo) {
                $productData[$productInfo['product_id']] = [
                    'featured' => $productInfo['featured'] ?? 'nao',
                    'notes' => $productInfo['notes'] ?? null,
                    'order' => $index + 1,
                ];
            }
            $content->products()->sync($productData);
        }

        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        $content->delete();
    }
}
