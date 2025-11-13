<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIAssistantFeedback extends Model
{
    use HasFactory;

    protected $table = 'assistant_logs';

    protected $fillable = [
        'tenant_id',
        'assistant_id',
        'session_id',
        'query',
        'response',
        'rating',
        'expected_response',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(AIAssistant::class, 'assistant_id');
    }
}
