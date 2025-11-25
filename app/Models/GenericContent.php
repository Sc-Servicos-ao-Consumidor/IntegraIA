<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GenericContent extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'type',
        'content',
        'data',
        'meta',
    ];

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(GenericContentChunk::class);
    }
}
