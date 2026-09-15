<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /**
     * Registro de Mantenimiento de Flota (Preventivo y Correctivo).
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $tipo = $request->input('tipo');

        $query = Maintenance::with('vehicle')->orderByDesc('maintenance_date');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('workshop_name', 'like', "%{$term}%")
                    ->orWhere('details', 'like', "%{$term}%")
                    ->orWhereHas('vehicle', function ($vq) use ($term) {
                        $vq->where('plate', 'like', "%{$term}%");
                    });
            });
        }

        if ($tipo) {
            $query->where('maintenance_type', $tipo);
        }

        $mantenimientos = $query->paginate(15)->withQueryString();
        $totalCosto = Maintenance::sum('cost');
        $totalRegistros = Maintenance::count();
        $vehiculos = Vehicle::where('status', 'Activo')->orderBy('plate')->get();

        return view('mantenimientos.index', compact('mantenimientos', 'term', 'tipo', 'totalCosto', 'totalRegistros', 'vehiculos'));
    }

    /**
     * Registrar un nuevo mantenimiento.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'max:50'],
            'maintenance_date' => ['required', 'date'],
            'mileage' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'workshop_name' => ['required', 'string', 'max:150'],
            'details' => ['nullable', 'string'],
        ]);

        Maintenance::create($validated);

        // Actualizar odómetro del vehículo si es superior al actual
        $vehiculo = Vehicle::find($validated['vehicle_id']);
        if ($vehiculo && $validated['mileage'] > $vehiculo->current_mileage) {
            $vehiculo->update(['current_mileage' => $validated['mileage']]);
        }

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Registro de mantenimiento guardado con éxito.');
    }

    /**
     * Actualizar registro de mantenimiento.
     */
    public function update(Request $request, Maintenance $mantenimiento): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'max:50'],
            'maintenance_date' => ['required', 'date'],
            'mileage' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'workshop_name' => ['required', 'string', 'max:150'],
            'details' => ['nullable', 'string'],
        ]);

        $mantenimiento->update($validated);

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Mantenimiento actualizado correctamente.');
    }

    /**
     * Eliminar registro de mantenimiento.
     */
    public function destroy(Maintenance $mantenimiento): RedirectResponse
    {
        $mantenimiento->delete();

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Registro de mantenimiento eliminado.');
    }
}
