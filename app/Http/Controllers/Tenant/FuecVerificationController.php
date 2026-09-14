<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FuecDocument;
use Illuminate\Support\Facades\Storage;

class FuecVerificationController extends Controller
{
    /**
     * Muestra la página pública de verificación del FUEC al escanear el código QR.
     */
    public function verify(string $fuec_number)
    {
        $fuec = FuecDocument::with(['serviceOrder.client', 'serviceOrder.contract', 'serviceOrder.vehicle', 'serviceOrder.driver'])
            ->where('fuec_number', $fuec_number)
            ->firstOrFail();

        return view('tenants.fuec.verify', compact('fuec'));
    }

    /**
     * Descarga pública o autorizada del archivo PDF del FUEC.
     */
    public function download(string $fuec_number)
    {
        $fuec = FuecDocument::where('fuec_number', $fuec_number)->firstOrFail();

        if (! $fuec->pdf_path || ! Storage::disk('public')->exists($fuec->pdf_path)) {
            abort(404, 'El archivo PDF del FUEC no se encuentra disponible.');
        }

        return Storage::disk('public')->download(
            $fuec->pdf_path,
            "FUEC_{$fuec->fuec_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Descarga del archivo PDF del FUEC por ID.
     */
    public function downloadById(int $id)
    {
        $fuec = FuecDocument::findOrFail($id);

        if (! $fuec->pdf_path || ! Storage::disk('public')->exists($fuec->pdf_path)) {
            abort(404, 'El archivo PDF del FUEC no se encuentra disponible.');
        }

        return Storage::disk('public')->download(
            $fuec->pdf_path,
            "FUEC_{$fuec->fuec_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
