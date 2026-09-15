<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    public function index(Request $request): View
    {
        $fecha = $request->input('fecha');
        $vehiculoId = $request->input('vehicle_id');
        $estado = $request->input('estado');

        $query = PreoperationalChecklist::with(['vehicle', 'driver', 'serviceOrder'])
            ->orderByDesc('date')
            ->orderByDesc('time');

        if ($fecha) {
            $query->whereDate('date', $fecha);
        }

        if ($vehiculoId) {
            $query->where('vehicle_id', $vehiculoId);
        }

        if ($estado !== null && $estado !== '') {
            $query->where('is_approved', (bool) $estado);
        }

        $checklists = $query->paginate(15)->withQueryString();
        $totalHoy = PreoperationalChecklist::whereDate('date', now()->toDateString())->count();
        $aprobadosHoy = PreoperationalChecklist::whereDate('date', now()->toDateString())->where('is_approved', true)->count();
        $vehiculos = Vehicle::where('status', 'Activo')->orderBy('plate')->get();

        return view('checklists.index', compact('checklists', 'fecha', 'vehiculoId', 'estado', 'totalHoy', 'aprobadosHoy', 'vehiculos'));
    }

    public function show(PreoperationalChecklist $checklist): View
    {
        $checklist->load(['vehicle', 'driver', 'serviceOrder.client']);

        return view('checklists.show', compact('checklist'));
    }

    public function pdf(PreoperationalChecklist $checklist): Response
    {
        $checklist->load(['vehicle', 'driver', 'serviceOrder.client']);

        $pdf = Pdf::loadView('checklists.pdf', compact('checklist'))
            ->setPaper('letter', 'portrait');

        $filename = "Checklist_{$checklist->vehicle?->plate}_{$checklist->date?->format('Y-m-d')}.pdf";

        return $pdf->stream($filename);
    }
}
