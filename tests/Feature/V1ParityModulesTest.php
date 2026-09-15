<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\Route as TenantRoute;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderApprovalRequest;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class V1ParityModulesTest extends TestCase
{
    protected Tenant $tenant;

    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['id' => 'empresa_qa'],
            ['name' => 'Transportes QA S.A.S.', 'company_name' => 'Transportes QA S.A.S.']
        );

        tenancy()->initialize($this->tenant);

        $this->tenantUser = User::firstOrCreate(
            ['email' => 'operador_qa@transporte.test'],
            [
                'name' => 'Operador QA',
                'username' => 'operador_qa',
                'password' => Hash::make('secret123'),
                'is_active' => true,
            ]
        );
    }

    /**
     * Módulo 1: Catálogo Maestro de Rutas Frecuentes.
     */
    public function test_frequent_routes_crud_and_search(): void
    {
        $this->actingAs($this->tenantUser);

        // 1. Index
        $response = $this->get(route('rutas.index'));
        $response->assertStatus(200);
        $response->assertSee('Catálogo Maestro de Rutas');

        // 2. Store
        $storeResponse = $this->post(route('rutas.store'), [
            'name' => 'Ruta Escolar Norte',
            'origin' => 'Suba, Bogotá',
            'destination' => 'Chía, Cundinamarca',
            'estimated_distance_km' => 25.5,
            'estimated_duration_minutes' => 45,
            'route_type' => 'Escolar',
            'is_active' => 1,
            'notes' => 'Ruta exclusiva para colegios zona norte',
        ]);
        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('success');

        $ruta = TenantRoute::where('name', 'Ruta Escolar Norte')->first();
        $this->assertNotNull($ruta);
        $this->assertEquals('Suba, Bogotá', $ruta->origin);

        // 3. Search API
        $searchResponse = $this->get(route('rutas.search', ['query' => 'Suba']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonFragment(['name' => 'Ruta Escolar Norte']);

        // 4. Update
        $updateResponse = $this->put(route('rutas.update', $ruta->id), [
            'name' => 'Ruta Escolar Norte Actualizada',
            'origin' => 'Suba Pinar, Bogotá',
            'destination' => 'Chía Centro',
            'estimated_distance_km' => 28.0,
            'estimated_duration_minutes' => 50,
            'route_type' => 'Escolar',
            'is_active' => 1,
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals('Ruta Escolar Norte Actualizada', $ruta->fresh()->name);

        // 5. Destroy (Soft delete)
        $deleteResponse = $this->delete(route('rutas.destroy', $ruta->id));
        $deleteResponse->assertRedirect();
        $this->assertSoftDeleted('routes', ['id' => $ruta->id]);
    }

    /**
     * Módulo 2: Aliados Comerciales y Convenios.
     */
    public function test_allied_companies_crud_and_directory(): void
    {
        $this->actingAs($this->tenantUser);

        // 1. Index
        $response = $this->get(route('aliados.index'));
        $response->assertStatus(200);
        $response->assertSee('Empresas Aliadas');

        // 2. Store
        $storeResponse = $this->post(route('aliados.store'), [
            'name' => 'Expreso Aliado S.A.S.',
            'nit' => '901999888-1',
            'phone' => '3109998877',
            'email' => 'gerencia@expresoaliado.com',
            'address' => 'Zona Franca Bogotá',
            'contact_person' => 'Carlos Gerente',
            'status' => 'Activo',
        ]);
        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('success');

        $aliado = Partner::where('nit', '901999888-1')->first();
        $this->assertNotNull($aliado);

        // 3. Show
        $showResponse = $this->get(route('aliados.show', $aliado->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Expreso Aliado S.A.S.');

        // 4. Update
        $updateResponse = $this->put(route('aliados.update', $aliado->id), [
            'name' => 'Expreso Aliado Colombia S.A.S.',
            'nit' => '901999888-1',
            'phone' => '3109998877',
            'email' => 'gerencia@expresoaliado.com',
            'contact_person' => 'Carlos Pérez',
            'status' => 'Activo',
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals('Expreso Aliado Colombia S.A.S.', $aliado->fresh()->name);

        // 5. Destroy
        $deleteResponse = $this->delete(route('aliados.destroy', $aliado->id));
        $deleteResponse->assertRedirect();
        $this->assertSoftDeleted('partners', ['id' => $aliado->id]);
    }

    /**
     * Módulo 3: Auditoría Preoperacional y Generación de PDF.
     */
    public function test_preoperational_checklist_audit_and_pdf_generation(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'CHK-999'],
            [
                'brand' => 'Chevrolet',
                'line' => 'NPR',
                'model_year' => 2023,
                'vehicle_type' => 'Buseta',
                'passenger_capacity' => 24,
                'status' => 'Activo',
            ]
        );

        $driver = Employee::firstOrCreate(
            ['document_number' => '77889900'],
            [
                'name' => 'Juan Conductor',
                'employee_type' => 'Conductor',
                'driver_license_number' => 'LC-778899',
                'status' => 'Activo',
            ]
        );

        $checklist = PreoperationalChecklist::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'date' => now()->toDateString(),
            'time' => '06:00:00',
            'fluid_levels' => ['aceite_motor' => 'OK', 'refrigerante' => 'OK', 'liquido_frenos' => 'OK'],
            'lights_and_electrical' => ['luces_altas' => 'OK', 'direccionales' => 'OK', 'stop' => 'OK'],
            'tires_and_brakes' => ['presion_llantas' => 'OK', 'freno_servicio' => 'OK'],
            'safety_kit' => ['extintor' => 'OK', 'botiquin' => 'OK', 'gato' => 'OK'],
            'cabin_and_belts' => ['cinturones' => 'OK', 'espejos' => 'OK'],
            'mileage' => 45200.0,
            'is_approved' => true,
            'observations' => 'Vehículo en condiciones mecánicas óptimas para operar.',
            'driver_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        // 1. Index con filtros
        $indexResponse = $this->get(route('checklists.index', [
            'vehicle_id' => $vehicle->id,
            'estado' => '1',
        ]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('CHK-999');
        $indexResponse->assertSee('Aprobado');

        // 2. Show
        $showResponse = $this->get(route('checklists.show', $checklist->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('CHK-999');
        $showResponse->assertSee('45,200 Km');

        // 3. Descarga PDF (Stream)
        $pdfResponse = $this->get(route('checklists.pdf', $checklist->id));
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }

    /**
     * Módulo 4: Flujos de Aprobación Gerencial de Cancelación y Modificación.
     */
    public function test_managerial_approval_workflow(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900888777-1'],
            ['business_name' => 'Cliente Corporativo SAS', 'is_active' => true]
        );

        $orderNumber1 = 'ODS-TEST-APPR-'.uniqid();
        $order = ServiceOrder::create([
            'order_number' => $orderNumber1,
            'client_id' => $client->id,
            'origin' => 'Bogotá',
            'destination' => 'Villavicencio',
            'scheduled_start_time' => now()->addDays(1),
            'scheduled_end_time' => now()->addDays(1)->addHours(4),
            'passengers_count' => 10,
            'status' => 'Asignada',
        ]);

        // 1. Crear Solicitud Gerencial de Cancelación
        $storeResponse = $this->post(route('gerencial.aprobaciones.store'), [
            'service_order_id' => $order->id,
            'request_type' => 'Cancelacion',
            'reason' => 'Cliente solicita cancelación por cierre temporal de la vía al Llano.',
        ]);
        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('success');

        $solicitud = ServiceOrderApprovalRequest::where('service_order_id', $order->id)->first();
        $this->assertNotNull($solicitud);
        $this->assertEquals('Pendiente', $solicitud->status);

        // 2. Index de Aprobaciones
        $indexResponse = $this->get(route('gerencial.aprobaciones.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($orderNumber1);

        // 3. Aprobar Solicitud Gerencial
        $approveResponse = $this->post(route('gerencial.aprobaciones.approve', $solicitud->id), [
            'manager_notes' => 'Autorizado. Caso fortuito de fuerza mayor según política.',
        ]);
        $approveResponse->assertRedirect();

        $solicitud->refresh();
        $this->assertEquals('Aprobado', $solicitud->status);
        $this->assertEquals('Cancelada', $order->fresh()->status);
        $this->assertStringContainsString('Cancelación aprobada por gerencia', $order->fresh()->service_notes);

        // 4. Crear otra solicitud y probar Rechazo
        $orderNumber2 = 'ODS-TEST-APPR-'.uniqid();
        $order2 = ServiceOrder::create([
            'order_number' => $orderNumber2,
            'client_id' => $client->id,
            'origin' => 'Bogotá',
            'destination' => 'Tunja',
            'scheduled_start_time' => now()->addDays(2),
            'scheduled_end_time' => now()->addDays(2)->addHours(4),
            'passengers_count' => 5,
            'status' => 'Asignada',
        ]);

        $solicitud2 = ServiceOrderApprovalRequest::create([
            'service_order_id' => $order2->id,
            'requested_by_user_id' => $this->tenantUser->id,
            'request_type' => 'Cancelacion',
            'reason' => 'Solicitud extemporánea sin justificación.',
            'status' => 'Pendiente',
        ]);

        $rejectResponse = $this->post(route('gerencial.aprobaciones.reject', $solicitud2->id), [
            'manager_notes' => 'Denegado por no cumplir con la anticipación mínima de 24 horas.',
        ]);
        $rejectResponse->assertRedirect();

        $solicitud2->refresh();
        $this->assertEquals('Rechazado', $solicitud2->status);
        $this->assertEquals('Asignada', $order2->fresh()->status);
    }

    /**
     * Módulo 5: Reportes Gerenciales y Liquidación Contable.
     */
    public function test_financial_reports_and_order_settlement(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900555444-9'],
            ['business_name' => 'Empresa Minera Andes', 'is_active' => true]
        );

        $liqOrderNumber = 'ODS-LIQ-'.uniqid();
        $order = ServiceOrder::create([
            'order_number' => $liqOrderNumber,
            'client_id' => $client->id,
            'origin' => 'Medellín',
            'destination' => 'Rionegro',
            'scheduled_start_time' => now(),
            'scheduled_end_time' => now()->addHours(2),
            'start_mileage' => 10000.0,
            'end_mileage' => 10045.0,
            'passengers_count' => 15,
            'status' => 'Finalizada',
        ]);

        // 1. Consultar Reportes Gerenciales
        $indexResponse = $this->get(route('reportes.index', [
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
            'client_id' => $client->id,
        ]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($liqOrderNumber);
        $indexResponse->assertSee('Sin Liquidar');

        // 2. Liquidar Orden y Asignar Factura
        $invoiceNum = 'FAC-ELEC-'.uniqid();
        $liquidarResponse = $this->post(route('reportes.liquidar', $order->id), [
            'invoice_number' => $invoiceNum,
        ]);
        $liquidarResponse->assertRedirect();
        $liquidarResponse->assertSessionHas('success');

        $this->assertEquals($invoiceNum, $order->fresh()->invoice_number);

        // 3. Filtrar por facturadas
        $filteredResponse = $this->get(route('reportes.index', [
            'estado_facturacion' => 'facturada',
        ]));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee($invoiceNum);
    }
}
