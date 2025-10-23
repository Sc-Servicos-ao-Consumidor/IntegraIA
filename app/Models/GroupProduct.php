<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'codigo_padrao',
        'descricao',
        'observacao',
        'status',
        'tenant_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $attributes = [
        'status' => true,
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
