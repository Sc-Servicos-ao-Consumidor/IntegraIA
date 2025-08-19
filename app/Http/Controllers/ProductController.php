<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\GroupProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('groupProduct')->latest()->get();
        $groupProducts = GroupProduct::where('status', true)->get();

        return Inertia::render('Products/Manage', [
            'products' => $products,
            'groupProducts' => $groupProducts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => 'required|string|max:255',
            'descricao_breve' => 'nullable|string|max:255',
            'informacao_adicional' => 'nullable|string',
            'codigo_padrao' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'group_product_id' => 'nullable|exists:group_products,id',
            'url_imagem_principal' => 'nullable|url',
            'ean' => 'nullable|string|max:255',
            'qr_code' => 'nullable|string|max:255',
            'url_rede_social' => 'nullable|url',
            'quantidade_caixa' => 'nullable|string|max:255',
            'embalagem_tipo' => 'nullable|string|max:255',
            'embalagem_descricao' => 'nullable|string|max:255',
            'peso_liquido' => 'nullable|numeric|min:0',
            'peso_bruto' => 'nullable|numeric|min:0',
            'validade' => 'nullable|string|max:255',
            'valor' => 'nullable|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
            'catalogo' => 'boolean',
            'lancamento' => 'boolean',
            'status' => 'boolean',
        ]);

        // Generate ULID and slug if creating new product
        if (!$request->id) {
            $data['ulid'] = Str::ulid();
            $data['slug'] = Str::slug($data['descricao']);
        }

        Product::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back();
    }

    /**
     * Store a newly created group product.
     */
    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'descricao' => 'required|string|max:255|unique:group_products,descricao',
            'codigo_padrao' => 'nullable|string|max:255',
            'observacao' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $data['ulid'] = Str::ulid();

        GroupProduct::create($data);

        return redirect()->back();
    }
}
