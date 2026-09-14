<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceIncident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverIncidentController extends Controller
{
    /**
     * Reportar un incidente o novedad operativa durante el servicio.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_order_id' => 'nullable|exists:service_orders,id',
            'incident_type' => 'required|in:Mecanica,Trafico,Accidente,Pasajero,Clima,Otro',
            'description' => 'required|string|min:5',
            'photos' => 'nullable|array',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'No se encontró el perfil de conductor.',
            ], 403);
        }

        $incident = ServiceIncident::create([
            'service_order_id' => $request->service_order_id,
            'driver_id' => $employee->id,
            'vehicle_id' => $request->vehicle_id,
            'incident_type' => $request->incident_type,
            'description' => $request->description,
            'photos' => $request->photos ?? [],
            'reported_at' => now(),
            'status' => 'Abierta',
        ]);

        return response()->json([
            'message' => 'Novedad / Incidente reportado exitosamente.',
            'incident' => $incident,
        ], 201);
    }
}
