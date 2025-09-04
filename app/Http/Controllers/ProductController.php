<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\GroupProduct;
use App\Models\Recipe;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with([
            'groupProduct', 
            'images' => function($query) {
                $query->active()->ordered();
            },
            'packagings',
            'recipes:id,recipe_name as descricao',
            'contents:id,nome_conteudo as descricao'
        ])->latest()->get();
        
        $groupProducts = GroupProduct::all();
        $recipes = Recipe::select('id', 'recipe_name as descricao')->get();
        $contents = Content::where('status', true)->select('id', 'nome_conteudo as descricao')->get();

        return Inertia::render('Products/Manage', [
            'products' => $products,
            'groupProducts' => $groupProducts,
            'recipes' => $recipes,
            'contents' => $contents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Product store request data:', $request->all());
        Log::info('Packaging data specifically:', $request->get('packaging'));
        
        // Validate product basic data
        $productData = $request->validate([
            'descricao' => 'required|string|max:255',
            'codigo_padrao' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'group_product_id' => 'nullable|exists:group_products,id',
            'marca' => 'nullable|string|max:255',
            'escolha_embalagem' => 'nullable|string|max:255',
            'prompt_uso_informacoes_produto' => 'nullable|string',
            'especificacao_produto' => 'nullable|string',
            'perfil_sabor' => 'nullable|string',
            'descricao_tabela_nutricional' => 'nullable|string',
            'descricao_lista_ingredientes' => 'nullable|string',
            'descricao_modos_preparo' => 'nullable|string',
            'descricao_rendimentos' => 'nullable|string',
            'ean' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        // Validate images data
        $imagesData = $request->validate([
            'images' => 'nullable|array',
            'images.*.image_type' => 'required|string|in:principal,embalagem,tabela_nutricional,lista_ingredientes,modos_preparo,rendimentos,outras',
            'images.*.nome' => 'nullable|string|max:255',
            'images.*.url' => 'required|url',
            'images.*.descricao' => 'nullable|string',
            'images.*.ordem' => 'nullable|integer|min:0',
            'images.*.ativo' => 'boolean',
            'images.*.valida_produtos_embalagem' => 'boolean',
        ]);

        // Validate relationship data
        $relationshipData = $request->validate([
            'recipe_ids' => 'nullable|array',
            'recipe_ids.*' => 'integer|exists:recipes,id',
            'content_ids' => 'nullable|array',
            'content_ids.*' => 'integer|exists:contents,id',
        ]);

        // Validate packaging data
        $packagingData = $request->validate([
            'packaging' => 'nullable|array',
            'packaging.prompt_especificacao_embalagens' => 'nullable|string',
            'packaging.packagings' => 'nullable|array',
            'packaging.packagings.*.descricao' => 'required|string|max:255',
            'packaging.packagings.*.codigo_padrao' => 'nullable|string|max:255',
            'packaging.packagings.*.sku' => 'nullable|string|max:255',
            'packaging.packagings.*.ean' => 'nullable|string|max:255',
            'packaging.packagings.*.quantidade_caixa' => 'nullable|string|max:255',
            'packaging.packagings.*.embalagem_tipo' => 'nullable|string|max:255',
            'packaging.packagings.*.peso_liquido' => 'nullable|numeric|min:0',
            'packaging.packagings.*.peso_bruto' => 'nullable|numeric|min:0',
            'packaging.packagings.*.validade' => 'nullable|date',
            'packaging.packagings.*.descontinuado' => 'boolean',
            'packaging.packagings.*.status' => 'boolean',
            'packaging.packagings.*.prompt_especificacao_embalagens' => 'nullable|string',
        ]);

        try {
            Log::info('Validation passed, starting database transaction');
            DB::beginTransaction();

            // Create or update product
            if ($request->id) {
                $product = Product::find($request->id);
                $product->update($productData);
            } else {
                $productData['ulid'] = Str::ulid();
                $productData['slug'] = Str::slug($productData['descricao']);
                $product = Product::create($productData);
            }

            // Handle images
            if (!empty($imagesData['images'])) {
                foreach ($imagesData['images'] as $imageData) {
                    if (!empty($imageData['url'])) {
                        $product->images()->create(array_merge($imageData, [
                            'ordem' => $imageData['ordem'] ?? 0,
                            'ativo' => $imageData['ativo'] ?? true,
                            'valida_produtos_embalagem' => $imageData['valida_produtos_embalagem'] ?? false,
                        ]));
                    }
                }
            }

            // Handle recipe relationships
            if ($request->has('recipe_ids') && is_array($request->recipe_ids)) {
                Log::info('Syncing recipes:', $request->recipe_ids);
                $product->recipes()->sync($request->recipe_ids);
            }

            // Handle content relationships
            if ($request->has('content_ids') && is_array($request->content_ids)) {
                Log::info('Syncing contents:', $request->content_ids);
                $product->contents()->sync($request->content_ids);
            }

            // Handle packaging data
            if ($request->has('packaging') && is_array($request->packaging)) {
                Log::info('Handling packaging data:', $request->packaging);
                
                // Handle multiple packagings
                if (isset($request->packaging['packagings']) && is_array($request->packaging['packagings'])) {

                    // Delete existing packagings that are not in the new list
                    $existingPackagingIds = $product->packagings()->pluck('id')->toArray();
                    $newPackagingIds = collect($request->packaging['packagings'])
                        ->pluck('id')
                        ->filter(function($id) { return is_numeric($id) && $id > 0; })
                        ->toArray();
                    
                    $packagingsToDelete = array_diff($existingPackagingIds, $newPackagingIds);
                    if (!empty($packagingsToDelete)) {
                        $product->packagings()->whereIn('id', $packagingsToDelete)->delete();
                    }
                    
                    // Update existing packagings
                    foreach ($request->packaging['packagings'] as $packagingData) {
                        
                        if (isset($packagingData['id']) && is_numeric($packagingData['id']) && $packagingData['id'] > 0) {
                            // Update existing packaging
                            $packaging = $product->packagings()->find($packagingData['id']);
                            if ($packaging) {
                                $packaging->update($packagingData);
                            }
                        }
                    }
                } else {
                    // Handle legacy single packaging approach or create default packaging
                    $packaging = $product->packagings()->first();
                    if (!$packaging) {
                        // Create default packaging for new products
                        $packaging = $product->packagings()->create([
                            'ulid' => Str::ulid(),
                            'codigo_padrao' => $product->codigo_padrao,
                            'sku' => $product->sku,
                            'descricao' => 'Embalagem ' . $product->descricao,
                            'status' => true,
                        ]);
                    }
                    
                    // Update with any provided data
                    if (isset($request->packaging['prompt_especificacao_embalagens'])) {
                        $packaging->update([
                            'prompt_especificacao_embalagens' => $request->packaging['prompt_especificacao_embalagens'],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produto salvo com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao salvar produto: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back();
    }
}
