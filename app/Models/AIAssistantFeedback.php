<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIAssistantFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_assistant_feedback';

    protected $fillable = [
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
}
