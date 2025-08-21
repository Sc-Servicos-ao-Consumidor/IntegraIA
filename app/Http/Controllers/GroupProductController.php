<?php

namespace App\Http\Controllers;

use App\Models\GroupProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GroupProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => 'required|string|max:255|unique:group_products,descricao',
            'codigo_padrao' => 'nullable|string|max:255',
            'observacao' => 'nullable|string|max:255',
        ]);

        $data['ulid'] = Str::ulid();
        $data['status'] = true; // Always set status to true

        GroupProduct::create($data);

        return redirect()->back()->with('success', 'Grupo de produtos criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroupProduct $groupProduct)
    {
        $data = $request->validate([
            'descricao' => 'required|string|max:255|unique:group_products,descricao,' . $groupProduct->id,
            'codigo_padrao' => 'nullable|string|max:255',
            'observacao' => 'nullable|string|max:255',
        ]);

        $data['status'] = true; // Always set status to true

        $groupProduct->update($data);

        return redirect()->back()->with('success', 'Grupo de produtos atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroupProduct $groupProduct)
    {
        // Check if group has products
        if ($groupProduct->products()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'Não é possível excluir um grupo que possui produtos associados.']);
        }

        $groupProduct->delete();

        return redirect()->back()->with('success', 'Grupo de produtos excluído com sucesso!');
    }
}
