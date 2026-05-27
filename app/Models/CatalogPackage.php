<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_product_id',
        'sku_package',
        'sku_package_name',
        'package_description',
        'gross_weight',
        'net_weight',
        'ean',
        'package_img_url',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight' => 'decimal:4',
            'net_weight' => 'decimal:4',
        ];
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
    }
}
