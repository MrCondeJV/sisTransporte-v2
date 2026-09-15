<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FuecDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuecController extends Controller
{
    /**
     * Catálogo y Registro Oficial de Documentos FUEC (Formato Único de Extracto de Contrato).
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $estado = $request->input('estado');

        $query = FuecDocument::with(['serviceOrder.client', 'serviceOrder.vehicle', 'serviceOrder.driver'])
            ->orderByDesc('issue_date');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('fuec_number', 'like', "%{$term}%")
                    ->orWhere('resolution_number', 'like', "%{$term}%")
                    ->orWhereHas('serviceOrder.client', function ($cq) use ($term) {
                        $cq->where('business_name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('serviceOrder.vehicle', function ($vq) use ($term) {
                        $vq->where('plate', 'like', "%{$term}%");
                    });
            });
        }

        if ($estado) {
            $query->where('status', $estado);
        }

        $totalFuecs = FuecDocument::count();
        $fuecsVigentes = FuecDocument::where('status', 'Vigente')->count();
        $documentos = $query->paginate(15)->withQueryString();

        return view('fuec.index', compact('documentos', 'term', 'estado', 'totalFuecs', 'fuecsVigentes'));
    }
}
