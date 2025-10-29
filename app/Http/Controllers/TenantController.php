<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Switch the active tenant for the authenticated user.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $tenantId = (int) $request->input('tenant_id');

        if (! $user) {
            abort(401);
        }

        $isAllowed = $user->tenants()->whereKey($tenantId)->exists();
        if (! $isAllowed) {
            abort(403, 'You do not have access to this tenant.');
        }

        $request->session()->put('tenant_id', $tenantId);

        return back();
    }
}
