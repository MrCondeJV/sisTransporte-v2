<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantForApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        // 1. Check X-Tenant header
        $tenantId = $request->header('X-Tenant') ?? $request->header('X-Tenant-Id');

        // 2. Check query or body parameter
        if (! $tenantId) {
            $tenantId = $request->input('tenant_id') ?? $request->input('tenant');
        }

        // 3. Check subdomain if not provided via header
        if (! $tenantId) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) >= 2 && ! in_array($parts[0], ['www', 'api', 'localhost'])) {
                $subdomain = $parts[0];
                $tenant = Tenant::where('id', $subdomain)->first();
                if ($tenant) {
                    $tenantId = $tenant->id;
                }
            }
        }

        if (! $tenantId) {
            return response()->json([
                'error' => 'Tenant identification required',
                'message' => 'Please provide the X-Tenant header or specify a tenant subdomain.',
            ], 400);
        }

        $tenant = Tenant::where('id', $tenantId)->first();

        if (! $tenant) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => "Tenant '{$tenantId}' does not exist.",
            ], 404);
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
