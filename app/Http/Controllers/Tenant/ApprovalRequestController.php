<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ServiceOrderApprovalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalRequestController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');

        $query = ServiceOrderApprovalRequest::with(['serviceOrder.client', 'requestedBy', 'reviewedBy'])
            ->orderByDesc('created_at');

        if ($estado) {
            $query->where('status', $estado);
        }

        if ($tipo) {
            $query->where('request_type', $tipo);
        }

        $solicitudes = $query->paginate(15)->withQueryString();

        $pendientesCount = ServiceOrderApprovalRequest::where('status', 'Pendiente')->count();
        $aprobadasCount = ServiceOrderApprovalRequest::where('status', 'Aprobado')->count();
        $rechazadasCount = ServiceOrderApprovalRequest::where('status', 'Rechazado')->count();

        return view('gerencial.aprobaciones', compact(
            'solicitudes',
            'estado',
            'tipo',
            'pendientesCount',
            'aprobadasCount',
            'rechazadasCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_order_id' => 'required|exists:service_orders,id',
            'request_type' => 'required|in:Cancelacion,Modificacion',
            'reason' => 'required|string|min:5|max:1000',
            'proposed_changes' => 'nullable|array',
        ]);

        $solicitud = ServiceOrderApprovalRequest::create([
            'service_order_id' => $validated['service_order_id'],
            'requested_by_user_id' => auth()->id(),
            'request_type' => $validated['request_type'],
            'reason' => $validated['reason'],
            'proposed_changes' => $validated['proposed_changes'] ?? null,
            'status' => 'Pendiente',
        ]);

        return back()->with('success', 'Solicitud gerencial #'.$solicitud->id.' enviada correctamente para revisión.');
    }

    public function approve(Request $request, ServiceOrderApprovalRequest $approval): RedirectResponse
    {
        if ($approval->status !== 'Pendiente') {
            return back()->with('error', 'Esta solicitud ya ha sido procesada previamente.');
        }

        $validated = $request->validate([
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'status' => 'Aprobado',
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? 'Aprobado por gerencia de operaciones.',
        ]);

        $order = $approval->serviceOrder;

        if ($approval->request_type === 'Cancelacion') {
            $order->update([
                'status' => 'Cancelada',
                'service_notes' => ($order->service_notes ? $order->service_notes."\n" : '').
                    '[Cancelación aprobada por gerencia el '.now()->format('d/m/Y H:i').': '.$approval->reason.']',
            ]);
        } elseif ($approval->request_type === 'Modificacion' && ! empty($approval->proposed_changes)) {
            $order->update($approval->proposed_changes);
        }

        return back()->with('success', 'Solicitud gerencial #'.$approval->id.' aprobada exitosamente.');
    }

    public function reject(Request $request, ServiceOrderApprovalRequest $approval): RedirectResponse
    {
        if ($approval->status !== 'Pendiente') {
            return back()->with('error', 'Esta solicitud ya ha sido procesada previamente.');
        }

        $validated = $request->validate([
            'manager_notes' => 'required|string|min:5|max:1000',
        ]);

        $approval->update([
            'status' => 'Rechazado',
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'],
        ]);

        return back()->with('success', 'Solicitud gerencial #'.$approval->id.' ha sido rechazada.');
    }
}
