<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assistant extends Model
{
    use HasFactory;

    protected $table = 'assistants';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'system_prompt',
        'default',
        'tools',
        'status',
    ];

    protected $casts = [
        'default' => 'boolean',
        'status' => 'boolean',
        'tools' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssistantLog::class, 'assistant_id');
    }
}
