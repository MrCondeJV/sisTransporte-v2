<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $query = Partner::withCount(['vehicles', 'employees', 'serviceOrders'])->orderBy('name');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('nit', 'like', "%{$term}%")
                    ->orWhere('contact_person', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $aliados = $query->paginate(15)->withQueryString();
        $totalAliados = Partner::count();
        $aliadosActivos = Partner::where('status', 'Activo')->count();

        return view('aliados.index', compact('aliados', 'term', 'totalAliados', 'aliadosActivos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $aliado = Partner::create($validated);

        return redirect()->route('aliados.index')
            ->with('success', "Aliado comercial '{$aliado->name}' registrado exitosamente.");
    }

    public function show(Partner $aliado): View
    {
        $aliado->load(['vehicles', 'employees', 'serviceOrders.client']);

        return view('aliados.show', compact('aliado'));
    }

    public function update(Request $request, Partner $aliado): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $aliado->update($validated);

        return redirect()->route('aliados.index')
            ->with('success', "Datos de '{$aliado->name}' actualizados.");
    }

    public function destroy(Partner $aliado): RedirectResponse
    {
        $nombre = $aliado->name;
        $aliado->delete();

        return redirect()->route('aliados.index')
            ->with('success', "Aliado '{$nombre}' eliminado satisfactoriamente.");
    }
}
