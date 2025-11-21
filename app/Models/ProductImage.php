<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_type',
        'nome',
        'url',
        'descricao',
        'alt_text',
        'ordem',
        'mime_type',
        'tamanho',
        'largura',
        'altura',
        'ativo',
        'valida_produtos_embalagem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'valida_produtos_embalagem' => 'boolean',
        'tamanho' => 'integer',
        'largura' => 'integer',
        'altura' => 'integer',
        'ordem' => 'integer',
    ];

    /**
     * Get the product that owns this image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get only active images.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope to get images by type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('image_type', $type);
    }

    /**
     * Scope to get images ordered by display order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('ordem');
    }

    /**
     * Get available image types.
     */
    public static function getImageTypes(): array
    {
        return [
            'principal' => 'Imagem Principal',
            'embalagem' => 'Embalagem',
            'tabela_nutricional' => 'Tabela Nutricional',
            'lista_ingredientes' => 'Lista de Ingredientes',
            'modos_preparo' => 'Modos de Preparo',
            'rendimentos' => 'Rendimentos',
            'outras' => 'Outras',
        ];
    }

    /**
     * Get human-readable image type.
     */
    public function getImageTypeNameAttribute(): string
    {
        $types = self::getImageTypes();

        return $types[$this->image_type] ?? 'Desconhecido';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): ?string
    {
        if (! $this->tamanho) {
            return null;
        }

        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get image dimensions formatted.
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->largura && $this->altura) {
            return $this->largura.' x '.$this->altura.' px';
        }

        return null;
    }

    /**
     * Check if image is valid for packaged products.
     */
    public function isValidForPackaging(): bool
    {
        return $this->valida_produtos_embalagem;
    }

    /**
     * Get full image URL (assuming it might be relative).
     */
    public function getFullUrlAttribute(): string
    {
        // If URL is already absolute, return as is
        if (filter_var($this->url, FILTER_VALIDATE_URL)) {
            return $this->url;
        }

        // Otherwise, prepend storage URL
        return asset('storage/'.$this->url);
    }
}
