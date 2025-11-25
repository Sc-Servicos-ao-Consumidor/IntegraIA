<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class GenericContentChunk extends Model
{
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'generic_content_id',
        'chunk_type',
        'content',
        'position',
        'embedding',
    ];

    protected $casts = [
        'embedding' => Vector::class,
    ];
}
