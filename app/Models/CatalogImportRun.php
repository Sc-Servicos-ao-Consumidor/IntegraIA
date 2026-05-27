<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogImportRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'catalog_import_status_id',
        'file_name',
        'total_rows',
        'created_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CatalogImportStatus::class, 'catalog_import_status_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(CatalogImportError::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
