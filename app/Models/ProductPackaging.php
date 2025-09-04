<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'ulid',
        'codigo_padrao',
        'sku',
        'ean',
        'descricao',
        'quantidade_caixa',
        'embalagem_tipo',
        'peso_liquido',
        'peso_bruto',
        'validade',
        'descontinuado',
        'status',
        'prompt_especificacao_embalagens',
    ];

    protected $casts = [
        'peso_liquido' => 'decimal:2',
        'peso_bruto' => 'decimal:2',
        'validade' => 'date',
        'descontinuado' => 'boolean',
        'status' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


