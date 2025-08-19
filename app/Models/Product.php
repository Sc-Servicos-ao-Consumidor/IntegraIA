<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'slug',
        'codigo_padrao',
        'sku',
        'group_product_id',
        'url_imagem_principal',
        'descricao',
        'descricao_breve',
        'informacao_adicional',
        'ean',
        'qr_code',
        'url_rede_social',
        'quantidade_caixa',
        'embalagem_tipo',
        'embalagem_descricao',
        'peso_liquido',
        'peso_bruto',
        'validade',
        'valor',
        'desconto',
        'catalogo',
        'lancamento',
        'status',
    ];

    protected $casts = [
        'peso_liquido' => 'decimal:2',
        'peso_bruto' => 'decimal:2',
        'valor' => 'decimal:2',
        'desconto' => 'decimal:2',
        'catalogo' => 'boolean',
        'lancamento' => 'boolean',
        'status' => 'boolean',
    ];

    public function groupProduct(): BelongsTo
    {
        return $this->belongsTo(GroupProduct::class);
    }

    /**
     * The recipes that use this product.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)
            ->withPivot([
                'quantity',
                'unit', 
                'ingredient_type',
                'preparation_notes',
                'optional',
                'order'
            ])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Get recipes where this product is a main ingredient.
     */
    public function mainRecipes(): BelongsToMany
    {
        return $this->recipes()->wherePivot('ingredient_type', 'main');
    }

    /**
     * Get recipes where this product is a supporting ingredient.
     */
    public function supportingRecipes(): BelongsToMany
    {
        return $this->recipes()->wherePivot('ingredient_type', 'supporting');
    }
}
