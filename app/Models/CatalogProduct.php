<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;

class CatalogProduct extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'tenant_id',
        'codigo_padrao',
        'sku',
        'product_name',
        'product_description',
        'product_img_url',
        'category_name',
        'sub_category_name',
        'line_name',
        'brand_name',
        'searchable_text',
        'embedding',
        'embedded_at',
    ];

    protected function casts(): array
    {
        return [
            'embedded_at' => 'datetime',
        ];
    }

    public function packages(): HasMany
    {
        return $this->hasMany(CatalogPackage::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeWithoutEmbedding(Builder $query): void
    {
        $query->whereNull('embedding');
    }

    public function scopeStale(Builder $query): void
    {
        $query->whereNull('embedded_at')->orWhereColumn('embedded_at', '<', 'updated_at');
    }
}
