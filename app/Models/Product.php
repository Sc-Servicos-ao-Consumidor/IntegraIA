<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Product extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'ulid',
        'slug',
        'codigo_padrao',
        'sku',
        'group_product_id',
        'marca',
        'prompt_uso_informacoes_produto', 
        'especificacao_produto', 
        'perfil_sabor', 
        'descricao_tabela_nutricional', 
        'descricao_lista_ingredientes', 
        'descricao_modos_preparo', 
        'descricao_rendimentos', 
        'descricao',
        'descricao_breve',
        'informacao_adicional',
        'ean',
        'dicas_utilizacao',
        'embalagem_tipo',
        'embalagem_descricao',
        'nome_responsavel',
        'status',
        'produto_familia_id',
        'produto_grupo_id',
        'produto_linha_id',
        'produto_sub_linha_id',
        'embedding',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the embedding as a Vector object
     */
    public function getEmbeddingVector(): ?Vector
    {
        return $this->embedding ? new Vector($this->embedding) : null;
    }

    public function groupProduct(): BelongsTo
    {
        return $this->belongsTo(GroupProduct::class);
    }

    

    /**
     * Get all product images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }

    /**
     * Get active product images.
     */
    public function activeImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)->active()->ordered();
    }

    /**
     * Get main product image.
     */
    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->ofType('principal')
            ->active()
            ->ordered();
    }

    /**
     * Get packaging images.
     */
    public function packagingImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->ofType('embalagem')
            ->active()
            ->ordered();
    }

    /**
     * Get nutritional table image.
     */
    public function nutritionalImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->ofType('tabela_nutricional')
            ->active()
            ->ordered();
    }

    /**
     * Get ingredients list image.
     */
    public function ingredientsImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->ofType('lista_ingredientes')
            ->active()
            ->ordered();
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
                'order',
                'top_dish'
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

    /**
     * The contents that feature this product.
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class)
            ->withPivot(['featured', 'order', 'notes'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Get contents where this product is featured.
     */
    public function featuredContents(): BelongsToMany
    {
        return $this->contents()->wherePivot('featured', 'sim');
    }
}
