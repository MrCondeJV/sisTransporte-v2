<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelController extends Controller
{
    /**
     * Bitácora de Combustible y Eficiencia de Flota.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $query = FuelRefill::with(['vehicle', 'driver'])->orderByDesc('refill_date');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('gas_station_name', 'like', "%{$term}%")
                    ->orWhereHas('vehicle', function ($vq) use ($term) {
                        $vq->where('plate', 'like', "%{$term}%");
                    });
            });
        }

        $tanqueos = $query->paginate(15)->withQueryString();
        $totalGalones = FuelRefill::sum('gallons');
        $totalGasto = FuelRefill::sum('total_cost');
        $vehiculos = Vehicle::where('status', 'Activo')->orderBy('plate')->get();
        $conductores = Employee::orderBy('name')->get();

        return view('combustible.index', compact('tanqueos', 'term', 'totalGalones', 'totalGasto', 'vehiculos', 'conductores'));
    }

    /**
     * Registrar un nuevo tanqueo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'refill_date' => ['required', 'date'],
            'gallons' => ['required', 'numeric', 'min:0.1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'odometer_mileage' => ['required', 'numeric', 'min:0'],
            'gas_station_name' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['price_per_gallon'] = $validated['gallons'] > 0 ? ($validated['total_cost'] / $validated['gallons']) : 0;

        FuelRefill::create($validated);

        // Actualizar odómetro del vehículo si es mayor
        $vehiculo = Vehicle::find($validated['vehicle_id']);
        if ($vehiculo && $validated['odometer_mileage'] > $vehiculo->current_mileage) {
            $vehiculo->update(['current_mileage' => $validated['odometer_mileage']]);
        }

        return redirect()->route('combustible.index')
            ->with('success', 'Registro de combustible guardado con éxito.');
    }

    /**
     * Actualizar registro de tanqueo.
     */
    public function update(Request $request, FuelRefill $combustible): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:employees,id'],
            'refill_date' => ['required', 'date'],
            'gallons' => ['required', 'numeric', 'min:0.1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'odometer_mileage' => ['required', 'numeric', 'min:0'],
            'gas_station_name' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['price_per_gallon'] = $validated['gallons'] > 0 ? ($validated['total_cost'] / $validated['gallons']) : 0;

        $combustible->update($validated);

        return redirect()->route('combustible.index')
            ->with('success', 'Registro de combustible actualizado correctamente.');
    }

    /**
     * Eliminar registro de combustible.
     */
    public function destroy(FuelRefill $combustible): RedirectResponse
    {
        $combustible->delete();

        return redirect()->route('combustible.index')
            ->with('success', 'Registro de combustible eliminado.');
    }
}
