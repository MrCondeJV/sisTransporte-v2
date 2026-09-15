<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Route as TenantRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $tipo = $request->input('tipo');

        $query = TenantRoute::orderBy('name');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('origin', 'like', "%{$term}%")
                    ->orWhere('destination', 'like', "%{$term}%");
            });
        }

        if ($tipo) {
            $query->where('route_type', $tipo);
        }

        $rutas = $query->paginate(15)->withQueryString();
        $totalRutas = TenantRoute::count();
        $rutasActivas = TenantRoute::where('status', 'Activa')->count();

        return view('rutas.index', compact('rutas', 'term', 'tipo', 'totalRutas', 'rutasActivas'));
    }

    public function create(): View
    {
        return view('rutas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'route_type' => ['required', 'in:Urbana,Rural,Intermunicipal,Escolar,Empresarial,Turismo'],
            'estimated_distance_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:Activa,Inactiva'],
            'is_active' => ['nullable'],
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = $request->input('is_active', true) ? 'Activa' : 'Inactiva';
        }
        unset($validated['is_active']);

        $ruta = TenantRoute::create($validated);

        return redirect()->route('rutas.index')
            ->with('success', "Ruta frecuente '{$ruta->name}' registrada con éxito.");
    }

    public function edit(TenantRoute $ruta): View
    {
        return view('rutas.edit', compact('ruta'));
    }

    public function update(Request $request, TenantRoute $ruta): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'route_type' => ['required', 'in:Urbana,Rural,Intermunicipal,Escolar,Empresarial,Turismo'],
            'estimated_distance_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:Activa,Inactiva'],
            'is_active' => ['nullable'],
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = $request->input('is_active', true) ? 'Activa' : 'Inactiva';
        }
        unset($validated['is_active']);

        $ruta->update($validated);

        return redirect()->route('rutas.index')
            ->with('success', "Ruta '{$ruta->name}' actualizada correctamente.");
    }

    public function destroy(TenantRoute $ruta): RedirectResponse
    {
        $nombre = $ruta->name;
        $ruta->delete();

        return redirect()->route('rutas.index')
            ->with('success', "Ruta '{$nombre}' eliminada del catálogo.");
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->query('q', '');
        $rutas = TenantRoute::where('status', 'Activa')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('origin', 'like', "%{$term}%")
                    ->orWhere('destination', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'origin', 'destination', 'estimated_distance_km']);

        return response()->json($rutas);
    }
}
