<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogPipelineStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function runs(): HasMany
    {
        return $this->hasMany(CatalogRequest::class);
    }

    public static function idFor(string $name): int
    {
        static $cache = [];

        if (app()->environment('testing')) {
            return static::firstOrCreate(['name' => $name])->id;
        }

        return $cache[$name] ??= static::firstOrCreate(['name' => $name])->id;
    }
}
