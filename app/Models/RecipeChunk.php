<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class RecipeChunk extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'recipe_id',
        'chunk_type',
        'content',
        'position',
        'embedding',
    ];

    protected $casts = [
        'embedding' => Vector::class,
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function getEmbeddingVector(): ?Vector
    {
        return $this->embedding ? new Vector($this->embedding) : null;
    }
}


