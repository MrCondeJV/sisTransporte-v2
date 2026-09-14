<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Tests\TestCase;

class PortalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            ['tenancy_db_name' => 'sistransporte_tenant_empresa1']
        );

        tenancy()->initialize($tenant);

        Employee::firstOrCreate(
            ['document_number' => '1020304050'],
            [
                'name' => 'Conductor de Pruebas',
                'employee_type' => 'Conductor',
                'status' => 'Activo',
            ]
        );
    }

    public function test_conductor_portal_renders_successfully(): void
    {
        $response = $this->get('/portal/conductor');
        $response->assertStatus(200);
        $response->assertSee('Terminal Móvil del Conductor');
        $response->assertSee('Checklist Preoperacional Diario');
    }

    public function test_cliente_portal_renders_successfully(): void
    {
        $response = $this->get('/portal/cliente');
        $response->assertStatus(200);
        $response->assertSee('Portal de Seguimiento de Clientes');
    }

    public function test_aliado_portal_renders_successfully(): void
    {
        $response = $this->get('/portal/aliado');
        $response->assertStatus(200);
        $response->assertSee('Portal de Aliados y Propietarios Vinculados');
    }

    public function test_conductor_can_start_and_finish_trip(): void
    {
        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'TST-999'],
            [
                'brand' => 'Chevrolet',
                'line' => 'NPR',
                'internal_number' => '999',
                'model_year' => 2024,
                'passenger_capacity' => 19,
                'current_mileage' => 10000,
                'status' => 'Activo',
            ]
        );

        $client = Client::firstOrCreate(
            ['document_number' => '900999888-1'],
            [
                'business_name' => 'Colegio Test',
                'type' => 'Empresa',
                'email' => 'colegio@test.com',
                'phone' => '3001112233',
                'status' => 'Activo',
            ]
        );

        $contract = Contract::firstOrCreate(
            ['contract_number' => 'CTR-TEST-999'],
            [
                'client_id' => $client->id,
                'contract_type' => 'escolar',
                'contract_object' => 'Transporte escolar de estudiantes',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'status' => 'vigente',
            ]
        );

        $order = ServiceOrder::create([
            'order_number' => 'ODS-PORTAL-' . uniqid(),
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'vehicle_id' => $vehicle->id,
            'origin' => 'Bogotá Norte',
            'destination' => 'Chía Cundinamarca',
            'scheduled_start_time' => now(),
            'scheduled_end_time' => now()->addHours(2),
            'status' => 'Pendiente',
        ]);

        // Iniciar viaje
        $resStart = $this->post("/portal/conductor/iniciar/{$order->id}", [
            'initial_odometer' => 10100,
        ]);
        $resStart->assertSessionHas('success');
        $this->assertEquals('En Progreso', $order->fresh()->status);
        $this->assertEquals(10100, $order->fresh()->start_mileage);

        // Finalizar viaje
        $resEnd = $this->post("/portal/conductor/finalizar/{$order->id}", [
            'final_odometer' => 10145,
        ]);
        $resEnd->assertSessionHas('success');
        $this->assertEquals('Finalizada', $order->fresh()->status);
        $this->assertEquals(10145, $order->fresh()->end_mileage);
        $this->assertEquals(10145, $vehicle->fresh()->current_mileage);
    }

    public function test_conductor_can_submit_checklist(): void
    {
        $vehicle = Vehicle::first();

        $response = $this->post('/portal/conductor/checklist', [
            'vehicle_id' => $vehicle->id,
            'current_odometer' => 10200,
            'frenos' => '1',
            'direccion' => '1',
            'luces' => '1',
            'llantas' => '1',
            'limpiabrisas' => '1',
            'espejos' => '1',
            'cinturones' => '1',
            'extintor' => '1',
            'botiquin' => '1',
            'equipo_carretera' => '1',
            'notes' => 'Inspección aprobada sin novedad',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('preoperational_checklists', [
            'vehicle_id' => $vehicle->id,
            'mileage' => 10200,
            'is_approved' => true,
        ]);
    }

    public function test_conductor_can_record_fuel(): void
    {
        $vehicle = Vehicle::first();

        $response = $this->post('/portal/conductor/combustible', [
            'vehicle_id' => $vehicle->id,
            'liters' => 15.5,
            'cost' => 165000,
            'odometer' => 10250,
            'gas_station' => 'Primax Autopista',
            'fuel_type' => 'diesel',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('fuel_refills', [
            'vehicle_id' => $vehicle->id,
            'gas_station_name' => 'Primax Autopista',
        ]);
    }
}
