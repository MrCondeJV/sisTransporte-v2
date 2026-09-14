<?php

namespace App\Services\Tenant;

use App\Models\Tenant\FuecDocument;
use App\Models\Tenant\ServiceOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FuecGeneratorService
{
    public function __construct(
        protected OperationValidationService $validationService
    ) {}

    /**
     * Genera el documento oficial FUEC (Formato Único de Extracto de Contrato) en PDF con código QR.
     *
     * @throws ValidationException
     */
    public function generate(ServiceOrder $order, array $options = []): FuecDocument
    {
        // 1. Cargar relaciones requeridas
        $order->loadMissing(['client', 'contract', 'vehicle', 'driver', 'supportDriver', 'partner']);

        // 2. Validar que la orden tenga vehículo y conductor elegibles
        if (! $order->vehicle_id) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Para emitir el FUEC es obligatorio asignar un vehículo.',
            ]);
        }

        if (! $order->driver_id) {
            throw ValidationException::withMessages([
                'driver_id' => 'Para emitir el FUEC es obligatorio asignar un conductor principal.',
            ]);
        }

        $this->validationService->validateAssignment($order->vehicle, $order->driver, $order->supportDriver);

        // 3. Estructurar número oficial FUEC (12-16 caracteres reglamentarios)
        $year = now()->format('Y');
        $territorial = $options['territorial_code'] ?? '315'; // 315 = Dirección Territorial Cundinamarca / Bogotá
        $resolution = $options['resolution_number'] ?? '0452';

        // Si ya existe un FUEC para esta orden, conservamos su número o generamos el siguiente consecutivo
        $existingFuec = $order->fuec;
        if ($existingFuec) {
            $fuecNumber = $existingFuec->fuec_number;
        } else {
            $consecutiveCount = FuecDocument::whereYear('issue_date', $year)->count() + 1;
            $consecutive = str_pad((string) $consecutiveCount, 4, '0', STR_PAD_LEFT);
            $fuecNumber = "{$territorial}{$resolution}{$year}{$consecutive}";
        }

        // 4. Generar URL de verificación y código QR SVG en memoria
        $verifyUrl = route('tenant.fuec.verify', ['fuec_number' => $fuecNumber], false);
        // Si estamos en contexto de subdominio, anteponer esquema y host
        if (! str_starts_with($verifyUrl, 'http')) {
            $verifyUrl = url($verifyUrl);
        }

        $qrSvg = (new QRCode)->render($verifyUrl);

        // 5. Preparar datos completos para el renderizado oficial
        $tenantName = tenant('name') ?? 'EMPRESA DE TRANSPORTE ESPECIAL S.A.S.';
        $tenantNit = tenant('nit') ?? '900.123.456-7';

        $viewData = [
            'fuec_number' => $fuecNumber,
            'resolution_number' => $resolution,
            'company' => [
                'name' => $tenantName,
                'nit' => $tenantNit,
            ],
            'client' => [
                'name' => $order->client->business_name,
                'document' => $order->client->document_number,
                'address' => $order->client->address,
                'phone' => $order->client->phone,
            ],
            'contract' => [
                'number' => $order->contract?->contract_number ?? 'OCASIONAL-'.$order->order_number,
                'type' => $order->contract?->contract_type ?? 'Ocasional / Grupo Específico',
                'object' => $order->contract?->contract_object ?? "Transporte terrestre automotor especial de pasajeros en la ruta {$order->origin} a {$order->destination}",
            ],
            'origin' => $order->origin,
            'destination' => $order->destination,
            'route_name' => $order->route_name,
            'passenger_contact' => $order->passenger_contact_name,
            'issue_date' => now()->startOfDay(),
            'expiration_date' => $order->scheduled_end_time ? $order->scheduled_end_time->endOfDay() : now()->addDays(3)->endOfDay(),
            'partner' => [
                'name' => $order->partner?->name,
                'nit' => $order->partner?->nit,
            ],
            'vehicle' => [
                'plate' => $order->vehicle->plate,
                'brand' => $order->vehicle->brand,
                'line' => $order->vehicle->line,
                'model_year' => $order->vehicle->model_year,
                'type' => $order->vehicle->vehicle_type,
                'capacity' => $order->vehicle->passenger_capacity,
                'internal_number' => $order->vehicle->internal_number ?? '001',
                'operation_card' => $order->vehicle->operation_card_number ?? 'TO-'.$order->vehicle->plate,
                'operation_card_exp' => $order->vehicle->operation_card_expiration?->format('d/m/Y'),
                'contractual_exp' => $order->vehicle->contractual_policy_expiration?->format('d/m/Y'),
                'extra_contractual_exp' => $order->vehicle->extra_contractual_policy_expiration?->format('d/m/Y'),
            ],
            'driver' => [
                'name' => $order->driver->name,
                'document' => $order->driver->document_number,
                'license_number' => $order->driver->driver_license_number,
                'license_category' => $order->driver->driver_license_category,
                'license_exp' => $order->driver->driver_license_expiration?->format('d/m/Y'),
            ],
            'support_driver' => $order->supportDriver ? [
                'name' => $order->supportDriver->name,
                'document' => $order->supportDriver->document_number,
                'license_number' => $order->supportDriver->driver_license_number,
                'license_category' => $order->supportDriver->driver_license_category,
                'license_exp' => $order->supportDriver->driver_license_expiration?->format('d/m/Y'),
            ] : null,
            'qr_svg' => $qrSvg,
        ];

        // 6. Renderizar PDF en memoria con DomPDF
        $pdf = Pdf::loadView('tenants.fuec.pdf', $viewData)
            ->setPaper('letter', 'portrait');

        // 7. Guardar en almacenamiento seguro del tenant
        $tenantId = tenant('id') ?? 'demo';
        $relativeDir = "tenants/{$tenantId}/fuecs";
        $fileName = "FUEC_{$fuecNumber}.pdf";
        $fullPath = "{$relativeDir}/{$fileName}";

        Storage::disk('public')->makeDirectory($relativeDir);
        Storage::disk('public')->put($fullPath, $pdf->output());

        // 8. Actualizar o Crear registro en BD Tenant
        return FuecDocument::updateOrCreate(
            ['service_order_id' => $order->id],
            [
                'fuec_number' => $fuecNumber,
                'resolution_number' => $resolution,
                'issue_date' => $viewData['issue_date']->toDateString(),
                'expiration_date' => $viewData['expiration_date']->toDateString(),
                'qr_code_content' => $verifyUrl,
                'pdf_path' => $fullPath,
                'status' => 'Emitido',
            ]
        );
    }
}
