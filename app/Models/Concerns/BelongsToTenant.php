<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (empty($model->tenant_id) && session()->has('tenant_id')) {
                $model->tenant_id = (int) session('tenant_id');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = (int) (session('tenant_id') ?? 0);
            $table = $builder->getModel()->getTable();
            if ($tenantId) {
                $builder->where($table . '.tenant_id', $tenantId);
            } elseif (auth()->check()) {
                // Authenticated but no tenant selected/available => return no rows
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = (int) (session('tenant_id') ?? 0);
        return $tenantId ? $query->where($query->getModel()->getTable() . '.tenant_id', $tenantId) : $query;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}


 