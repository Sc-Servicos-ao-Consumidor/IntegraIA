<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'catalog_pipeline_status_id',
        'contact_id',
        'session_id',
        'audio_url',
        'question',
        'search_results',
        'ai_answer',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'search_results' => 'array',
            'ai_answer' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CatalogPipelineStatus::class, 'catalog_pipeline_status_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
