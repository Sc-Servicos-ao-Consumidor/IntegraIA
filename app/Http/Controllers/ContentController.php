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
    public function index()
    {
        $contents = Content::with(['recipes', 'products'])->latest()->get();
        $recipes = Recipe::where('recipe_name', '!=', null)->orderBy('recipe_name')->get();
        $products = Product::where('status', true)->orderBy('descricao')->get();

        return Inertia::render('Contents/Manage', [
            'contents' => $contents,
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
            'nome_conteudo' => 'nullable|string|max:255',
            'content_code' => 'nullable|string|max:255|unique:contents,content_code,' . $request->id,
            'descricao_tabela_nutricional' => 'nullable|string',
            'descricao_lista_ingredientes' => 'nullable|string',
            'descricao_modos_preparo' => 'nullable|string',
            'descricao_rendimentos' => 'nullable|string',
            'imagem_tabela_nutricional' => 'nullable|url',
            'imagem_lista_ingredientes' => 'nullable|url',
            'imagem_modos_preparo' => 'nullable|url',
            'imagem_rendimentos' => 'nullable|url',
            'imagens_nutricionais_cadastradas' => 'nullable|array',
            'imagens_ingredientes_cadastradas' => 'nullable|array',
            'imagens_preparo_cadastradas' => 'nullable|array',
            'imagens_rendimentos_cadastradas' => 'nullable|array',
            'tipo_conteudo' => 'nullable|string|max:255',
            'pilares' => 'nullable|string|max:255',
            'canal' => 'nullable|string|max:255',
            'links_conteudo' => 'nullable|array',
            'cozinheiro' => 'nullable|boolean',
            'comprador' => 'nullable|boolean',
            'administrador' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'descricao_conteudo' => 'nullable|string',
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
