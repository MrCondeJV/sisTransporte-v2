<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverChecklistController extends Controller
{
    /**
     * Registrar Checklist Preoperacional diario del vehículo.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_order_id' => 'nullable|exists:service_orders,id',
            'mileage' => 'required|numeric|min:0',
            'is_approved' => 'boolean',
            'fluid_levels' => 'required|array',
            'lights_and_electrical' => 'required|array',
            'tires_and_brakes' => 'required|array',
            'safety_kit' => 'required|array',
            'cabin_and_belts' => 'required|array',
            'observations' => 'nullable|string',
            'driver_signature' => 'nullable|string',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'No se encontró el perfil de conductor.',
            ], 403);
        }

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        $checklist = PreoperationalChecklist::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $employee->id,
            'service_order_id' => $request->service_order_id,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'mileage' => $request->mileage,
            'is_approved' => $request->boolean('is_approved', true),
            'fluid_levels' => $request->fluid_levels,
            'lights_and_electrical' => $request->lights_and_electrical,
            'tires_and_brakes' => $request->tires_and_brakes,
            'safety_kit' => $request->safety_kit,
            'cabin_and_belts' => $request->cabin_and_belts,
            'observations' => $request->observations,
            'driver_signature' => $request->driver_signature,
        ]);

        // Sincronizar odómetro si la inspección es aprobada y la lectura es superior
        if ($checklist->is_approved && $request->mileage > $vehicle->current_mileage) {
            $vehicle->update(['current_mileage' => $request->mileage]);
        }

        return response()->json([
            'message' => 'Checklist preoperacional registrado exitosamente.',
            'checklist' => $checklist,
        ], 201);
    }
}
