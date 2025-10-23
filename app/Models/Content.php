<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Content extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'tenant_id',
        'nome_conteudo',
        'content_code',
        'tipo_conteudo',
        'pilares',
        'canal',
        'links_conteudo',
        'cozinheiro',
        'comprador',
        'administrador',
        'descricao_conteudo',
        'content_prompt',
        'status',
        'embedding'
    ];

    protected $casts = [
        'links_conteudo' => 'array',
        'cozinheiro' => 'boolean',
        'comprador' => 'boolean',
        'administrador' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the embedding as a Vector object
     */
    public function getEmbeddingVector(): ?Vector
    {
        return $this->embedding ? new Vector($this->embedding) : null;
    }

    /**
     * The recipes that belong to this content.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)
            ->withPivot(['order', 'top_dish'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * The products that belong to this content.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['featured', 'order', 'notes'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Get only featured products.
     */
    public function featuredProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('featured', 'sim');
    }
}
