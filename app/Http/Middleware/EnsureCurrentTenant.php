<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $currentTenantId = session('current_tenant_id');

            if (!$currentTenantId || !$user->tenants()->where('tenants.id', $currentTenantId)->exists()) {
                $tenant = $user->tenants()->first();
                if ($tenant) {
                    session(['current_tenant_id' => $tenant->id]);
                }
            }
        }

        return $next($request);
    }
}
