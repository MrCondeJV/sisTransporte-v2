<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuecDocument;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\FuecGeneratorService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FuecGenerationTest extends TestCase
{
    public function test_fuec_generation_qr_pdf_and_verification_workflow(): void
    {
        Storage::fake('public');

        // 1. Inicializar Tenant de prueba
        Tenant::find('test_fase4')?->delete();
        $tenant = Tenant::create([
            'id' => 'test_fase4',
            'name' => 'Transportes Especiales de Colombia S.A.S.',
            'nit' => '901999888-2',
        ]);
        $tenant->domains()->create(['domain' => 'fase4.localhost']);

        tenancy()->initialize($tenant);

        // 2. Crear Cliente
        $client = Client::create([
            'type' => 'Empresa',
            'business_name' => 'Universidad Nacional de Colombia',
            'document_number' => '899999063-3',
            'email' => 'transporte@unal.edu.co',
            'address' => 'Carrera 45 # 26-85, Bogotá',
            'phone' => '6013165000',
            'status' => 'Activo',
        ]);

        // 3. Crear Vehículo habilitado con documentos al día
        $vehicle = Vehicle::create([
            'plate' => 'FUE123',
            'brand' => 'Scania',
            'line' => 'Touring',
            'model_year' => 2025,
            'vehicle_type' => 'Bus',
            'passenger_capacity' => 40,
            'current_mileage' => 15000,
            'soat_expiration' => now()->addMonths(9),
            'technomechanical_expiration' => now()->addMonths(10),
            'contractual_policy_expiration' => now()->addMonths(6),
            'extra_contractual_policy_expiration' => now()->addMonths(6),
            'operation_card_expiration' => now()->addMonths(11),
            'status' => 'Activo',
        ]);

        // 4. Crear Conductor con licencia vigente
        $driver = Employee::create([
            'name' => 'Fernando Chofer Certificado',
            'document_number' => '80123456',
            'phone' => '3001234567',
            'email' => 'fernando@empresa.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LC-80123456',
            'driver_license_category' => 'C2',
            'driver_license_expiration' => now()->addYear(),
            'status' => 'Activo',
        ]);

        // 5. Crear Orden de Servicio
        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-FUEC-01',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Bogotá D.C. (Campus UNAL)',
            'destination' => 'Villa de Leyva, Boyacá',
            'route_name' => 'Salida Académica Geología Boyacá',
            'scheduled_start_time' => now()->addDay(),
            'scheduled_end_time' => now()->addDays(3),
            'passengers_count' => 38,
            'status' => 'Asignada',
        ]);

        // 6. Generar FUEC mediante FuecGeneratorService
        $service = app(FuecGeneratorService::class);
        $fuec = $service->generate($order, [
            'territorial_code' => '315',
            'resolution_number' => '0452',
        ]);

        // Aserciones sobre el registro en BD
        $this->assertInstanceOf(FuecDocument::class, $fuec);
        $this->assertDatabaseHas('fuec_documents', [
            'id' => $fuec->id,
            'service_order_id' => $order->id,
            'status' => 'Emitido',
        ]);

        // Formato número oficial de 12-16 dígitos: 315 (territorial) + 0452 (resolución) + año actual + 0001 (consecutivo)
        $year = now()->format('Y');
        $this->assertStringStartsWith("3150452{$year}", $fuec->fuec_number);
        $this->assertEquals(15, strlen($fuec->fuec_number));

        // Verificar existencia y contenido del archivo PDF
        $this->assertTrue(Storage::disk('public')->exists($fuec->pdf_path));
        $pdfContent = Storage::disk('public')->get($fuec->pdf_path);
        $this->assertStringStartsWith('%PDF-', $pdfContent, 'El archivo generado debe ser un PDF válido');

        // Verificar código QR
        $this->assertNotEmpty($fuec->qr_code_content);
        $this->assertStringContainsString($fuec->fuec_number, $fuec->qr_code_content);

        // 7. Prueba de Consecutivo Único Incremental
        $order2 = ServiceOrder::create([
            'order_number' => 'OS-2026-FUEC-02',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Bogotá D.C.',
            'destination' => 'Girardot, Cundinamarca',
            'scheduled_start_time' => now()->addDays(5),
            'scheduled_end_time' => now()->addDays(6),
            'passengers_count' => 25,
            'status' => 'Asignada',
        ]);

        $fuec2 = $service->generate($order2, [
            'territorial_code' => '315',
            'resolution_number' => '0452',
        ]);

        $this->assertNotEquals($fuec->fuec_number, $fuec2->fuec_number);
        $this->assertEquals("3150452{$year}0002", $fuec2->fuec_number);

        // 8. Prueba del Endpoint Público de Verificación QR
        $response = $this->get("http://fase4.localhost/fuec/verify/{$fuec->fuec_number}");
        $response->assertStatus(200);
        $response->assertSee('Documento Auténtico y Vigente');
        $response->assertSee($fuec->fuec_number);
        $response->assertSee('FUE123'); // Placa
        $response->assertSee('Fernando Chofer Certificado'); // Conductor
        $response->assertSee('Universidad Nacional de Colombia'); // Cliente

        // 9. Prueba de Descarga Pública del PDF
        $downloadResponse = $this->get("http://fase4.localhost/fuec/download/{$fuec->fuec_number}");
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-type', 'application/pdf');

        // 10. Prueba de Anulación
        $fuec->update(['status' => 'Anulado']);
        $anuladoResponse = $this->get("http://fase4.localhost/fuec/verify/{$fuec->fuec_number}");
        $anuladoResponse->assertStatus(200);
        $anuladoResponse->assertSee('Documento Anulado');

        tenancy()->end();
    }
}
