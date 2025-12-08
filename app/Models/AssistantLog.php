<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistantLog extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'assistant_logs';

    protected $fillable = [
        'tenant_id',
        'assistant_id',
        'session_id',
        'query',
        'response',
        'rating',
        'comment',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(Assistant::class, 'assistant_id');
    }
}
