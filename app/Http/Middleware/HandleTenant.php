<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleTenant
{
    /**
     * Ensure an authenticated user has a valid current tenant in session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Lazily load allowed tenant ids
            $allowedTenantIds = $user->tenants()->pluck('tenants.id')->all();

            // If no tenant in session or not allowed, set to first allowed
            $currentTenantId = (int) ($request->session()->get('tenant_id') ?? 0);

            if (!$currentTenantId || !in_array($currentTenantId, $allowedTenantIds, true)) {
                $newTenantId = $allowedTenantIds[0] ?? null;
                if ($newTenantId) {
                    $request->session()->put('tenant_id', $newTenantId);
                } else {
                    // User has no tenants; clear any stale value
                    $request->session()->forget('tenant_id');
                }
            }
        }

        return $next($request);
    }
}


