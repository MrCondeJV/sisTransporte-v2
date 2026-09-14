<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\FuelPerformanceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverFuelController extends Controller
{
    public function __construct(
        protected FuelPerformanceCalculator $calculator
    ) {}

    /**
     * Registrar una recarga de combustible y calcular rendimiento.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_order_id' => 'nullable|exists:service_orders,id',
            'refill_date' => 'nullable|date',
            'gallons' => 'required|numeric|gt:0',
            'total_cost' => 'required|numeric|gt:0',
            'odometer_mileage' => 'required|numeric|min:0',
            'gas_station_name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'No se encontró el perfil de conductor.',
            ], 403);
        }

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        $metrics = $this->calculator->computeRefillMetrics(
            $vehicle,
            (float) $request->odometer_mileage,
            (float) $request->gallons,
            (float) $request->total_cost
        );

        $refill = FuelRefill::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $employee->id,
            'service_order_id' => $request->service_order_id,
            'refill_date' => $request->refill_date ?? now()->toDateString(),
            'gallons' => $request->gallons,
            'total_cost' => $request->total_cost,
            'price_per_gallon' => $metrics['price_per_gallon'],
            'odometer_mileage' => $request->odometer_mileage,
            'distance_since_last_refill' => $metrics['distance_since_last_refill'],
            'calculated_performance' => $metrics['calculated_performance'],
            'gas_station_name' => $request->gas_station_name,
            'notes' => $request->notes,
        ]);

        // Sincronizar odómetro del vehículo si es mayor
        if ($request->odometer_mileage > $vehicle->current_mileage) {
            $vehicle->update(['current_mileage' => $request->odometer_mileage]);
        }

        return response()->json([
            'message' => 'Recarga de combustible registrada exitosamente.',
            'fuel_refill' => $refill,
            'performance_metrics' => [
                'distance_km' => $metrics['distance_since_last_refill'],
                'km_per_gallon' => $metrics['calculated_performance'],
                'price_per_gallon' => $metrics['price_per_gallon'],
                'is_anomalous' => $metrics['is_anomalous'],
                'warning' => $metrics['warning_message'],
            ],
        ], 201);
    }
}
