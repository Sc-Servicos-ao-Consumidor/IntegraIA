<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class ProductChunk extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'product_id',
        'chunk_type',
        'content',
        'position',
        'embedding',
    ];

    protected $casts = [
        'embedding' => Vector::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEmbeddingVector(): ?Vector
    {
        return $this->embedding ? new Vector($this->embedding) : null;
    }
}


