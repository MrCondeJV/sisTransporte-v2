<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForAppPanel
{
    /**
     * Inicializa la empresa (tenant) correspondiente según dominio, parámetro de consulta o sesión.
     * Permite acceder tanto por subdominios (producción / hosts) como directamente desde el dominio principal
     * en entornos locales donde los subdominios wildcard no resuelven por DNS en Windows.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', ['sistransporte-v2.test', 'localhost', '127.0.0.1']);

        // 1. Si NO es un dominio central, intentar resolver por tabla de dominios o subdominio
        if (! in_array($host, $centralDomains)) {
            $domainRecord = Domain::where('domain', $host)->first();
            if ($domainRecord && $domainRecord->tenant) {
                tenancy()->initialize($domainRecord->tenant);

                return $next($request);
            }

            // Detección por subdominio (ej: empresa1 de empresa1.sistransporte-v2.test)
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $subdomain = $parts[0];
                $tenant = Tenant::find($subdomain);
                if ($tenant) {
                    tenancy()->initialize($tenant);

                    return $next($request);
                }
            }
        }

        // 2. Si la petición es directamente para el panel central (/admin), login, logout o health check, NO inicializar tenancy
        if ($request->is('admin') || $request->is('admin/*') || $request->is('login') || $request->is('logout') || $request->is('up')) {
            return $next($request);
        }

        // 3. Peticiones de Livewire originadas desde /admin:
        if ($request->is('livewire*') || str_contains($request->path(), 'livewire')) {
            $referer = $request->header('referer') ?? '';
            // Si proviene del panel central /admin, NO inicializar tenancy
            if (str_contains($referer, '/admin')) {
                return $next($request);
            }
        }

        // 4. Resolver Tenant para el panel operativo (/app o Livewire de /app):
        // A. Parámetro de consulta ?tenant=xxx en request o en referer
        $tenantId = $request->query('tenant') ?? $request->input('tenant');
        if (! $tenantId && ($referer = $request->header('referer'))) {
            $query = parse_url($referer, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $queryParams);
                $tenantId = $queryParams['tenant'] ?? null;
            }
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                if ($request->hasSession()) {
                    $request->session()->put('active_tenant_id', $tenant->id);
                }
                tenancy()->initialize($tenant);

                return $next($request);
            }
        }

        // B. Sesión activa
        if ($request->hasSession() && ($savedTenantId = $request->session()->get('active_tenant_id'))) {
            $tenant = Tenant::find($savedTenantId);
            if ($tenant) {
                tenancy()->initialize($tenant);

                return $next($request);
            }
        }

        // C. Fallback para desarrollo local: cargar la empresa primaria (empresa1 o primera existente)
        $defaultTenant = Tenant::find('empresa1') ?? Tenant::first();
        if ($defaultTenant) {
            if ($request->hasSession()) {
                $request->session()->put('active_tenant_id', $defaultTenant->id);
            }
            tenancy()->initialize($defaultTenant);

            return $next($request);
        }

        abort(404, 'No se ha configurado ninguna empresa (tenant) en el sistema.');
    }
}
