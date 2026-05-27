<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_import_run_id',
        'row_number',
        'row_data',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'row_data' => 'array',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(CatalogImportRun::class, 'catalog_import_run_id');
    }
}
