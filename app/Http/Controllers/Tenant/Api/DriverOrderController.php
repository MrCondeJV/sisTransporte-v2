<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceOrder;
use App\Services\Tenant\OperationValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverOrderController extends Controller
{
    public function __construct(
        protected OperationValidationService $validationService
    ) {}

    /**
     * Listar órdenes asignadas al conductor autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'No se encontró un perfil de conductor asociado a este usuario.',
            ], 403);
        }

        $query = ServiceOrder::with([
            'client:id,business_name,first_name,last_name,phone',
            'vehicle:id,plate,internal_number,brand,model_year,current_mileage',
            'fuecDocument:id,service_order_id,fuec_number,status',
        ])->where(function ($q) use ($employee) {
            $q->where('driver_id', $employee->id)
                ->orWhere('support_driver_id', $employee->id);
        });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_start_time', $request->date);
        }

        $orders = $query->orderBy('scheduled_start_time')->get();

        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }

    /**
     * Ver detalles completos de una orden de servicio.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json(['error' => 'Forbidden', 'message' => 'Perfil no encontrado'], 403);
        }

        $order = ServiceOrder::with([
            'client',
            'contract',
            'vehicle',
            'fuecDocument',
            'checklists',
            'incidents',
        ])->where(function ($q) use ($employee) {
            $q->where('driver_id', $employee->id)
                ->orWhere('support_driver_id', $employee->id);
        })->find($id);

        if (! $order) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Orden de servicio no encontrada o no asignada a su perfil.',
            ], 404);
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Actualizar estado de la orden de servicio (e.g. En Progreso, Finalizada).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:En Progreso,Finalizada,Cancelada',
            'mileage' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (! $employee) {
            return response()->json(['error' => 'Forbidden', 'message' => 'Perfil no encontrado'], 403);
        }

        $order = ServiceOrder::where(function ($q) use ($employee) {
            $q->where('driver_id', $employee->id)
                ->orWhere('support_driver_id', $employee->id);
        })->find($id);

        if (! $order) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Orden de servicio no encontrada.',
            ], 404);
        }

        try {
            $this->validationService->validateStatusTransition($order, $request->status);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        $order->status = $request->status;

        if ($request->status === 'En Progreso') {
            $order->actual_start_time = now();
            if ($request->filled('mileage')) {
                $order->start_mileage = $request->mileage;
            }
        } elseif ($request->status === 'Finalizada') {
            $order->actual_end_time = now();
            if ($request->filled('mileage')) {
                $order->end_mileage = $request->mileage;
                if ($order->vehicle && $request->mileage > $order->vehicle->current_mileage) {
                    $order->vehicle->update(['current_mileage' => $request->mileage]);
                }
            }
        }

        if ($request->filled('notes')) {
            $order->service_notes = ($order->service_notes ? $order->service_notes."\n" : '').$request->notes;
        }

        $order->save();

        return response()->json([
            'message' => "Estado de la orden actualizado a {$order->status}.",
            'order' => $order->fresh(['vehicle', 'client']),
        ]);
    }
}
