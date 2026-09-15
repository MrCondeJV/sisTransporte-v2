<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantSwitchController extends Controller
{
    /**
     * Alternar la empresa activa en la sesión del usuario.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        $tenantId = $request->input('tenant_id');
        $tenant = Tenant::findOrFail($tenantId);

        $request->session()->put('active_tenant_id', $tenant->id);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        return back()->with('success', "Empresa activa cambiada a: {$tenant->name}");
    }
}
