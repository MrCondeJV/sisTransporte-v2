<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Services\Tenant\GpsTelemetryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverTelemetryController extends Controller
{
    public function __construct(
        protected GpsTelemetryService $telemetryService
    ) {}

    /**
     * Registrar un ping de ubicación GPS en tiempo real.
     */
    public function ping(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        $recordedAt = $request->filled('recorded_at')
            ? Carbon::parse($request->recorded_at)
            : now();

        $location = $this->telemetryService->recordLocation([
            'vehicle_id' => (int) $request->vehicle_id,
            'latitude' => (float) $request->latitude,
            'longitude' => (float) $request->longitude,
            'speed' => $request->filled('speed') ? (float) $request->speed : null,
            'heading' => $request->filled('heading') ? (float) $request->heading : null,
            'altitude' => $request->filled('altitude') ? (float) $request->altitude : null,
            'accuracy' => $request->filled('accuracy') ? (float) $request->accuracy : null,
            'device_timestamp' => $recordedAt,
        ]);

        return response()->json([
            'message' => 'Ubicación GPS registrada exitosamente.',
            'location' => $location,
        ], 201);
    }

    /**
     * Registrar un lote de coordenadas GPS almacenadas en buffer móvil sin conexión.
     */
    public function batch(Request $request): JsonResponse
    {
        $request->validate([
            'locations' => 'required|array|min:1',
            'locations.*.vehicle_id' => 'required|exists:vehicles,id',
            'locations.*.latitude' => 'required|numeric|between:-90,90',
            'locations.*.longitude' => 'required|numeric|between:-180,180',
            'locations.*.speed' => 'nullable|numeric|min:0',
            'locations.*.heading' => 'nullable|numeric|between:0,360',
            'locations.*.altitude' => 'nullable|numeric',
            'locations.*.accuracy' => 'nullable|numeric',
            'locations.*.recorded_at' => 'nullable|date',
        ]);

        $count = $this->telemetryService->recordBatch($request->locations);

        return response()->json([
            'message' => "Se registraron {$count} puntos de telemetría GPS.",
            'inserted_count' => $count,
        ], 201);
    }
}
