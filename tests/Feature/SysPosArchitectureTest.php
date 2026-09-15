<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SysPosArchitectureTest extends TestCase
{
    protected User $user;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            [
                'name' => 'Transportes Kemuel S.A.S.',
                'nit' => '901234567-8',
                'tenancy_db_name' => 'sistransporte_tenant_empresa1',
            ]
        );

        tenancy()->initialize($this->tenant);

        $this->user = User::firstOrCreate(
            ['email' => 'admin@sistransporte.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin123456'),
            ]
        );
    }

    public function test_login_page_renders_cleanly(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('sisTransporte');
        $response->assertSee('Ingresar a la Plataforma');
    }

    public function test_user_can_authenticate_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sistransporte.com',
            'password' => 'admin123456',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_dashboard_renders_with_kpis_and_banner(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Centro de Operaciones Activo');
        $response->assertSee('Flota Operativa');
        $response->assertSee('Órdenes del Día');
        $response->assertSee('FUEC Digital');
        $response->assertSee('Despachos y Órdenes Recientes');
    }

    public function test_vehicles_catalogue_renders_and_supports_filtering(): void
    {
        $this->actingAs($this->user);

        Vehicle::firstOrCreate(
            ['plate' => 'KML123'],
            [
                'brand' => 'Toyota',
                'line' => 'HiAce',
                'model_year' => 2024,
                'vehicle_type' => 'Microbus',
                'passenger_capacity' => 16,
                'status' => 'Activo',
            ]
        );

        $response = $this->get('/vehiculos?buscar=KML123');
        $response->assertStatus(200);
        $response->assertSee('Catálogo de Vehículos');
        $response->assertSee('KML123');
        $response->assertSee('Nuevo Vehículo');
    }

    public function test_can_create_vehicle_in_catalogue(): void
    {
        $this->actingAs($this->user);

        $testPlate = 'TST'.rand(100, 999);

        $response = $this->post('/vehiculos', [
            'plate' => $testPlate,
            'internal_number' => '99',
            'brand' => 'Chevrolet',
            'line' => 'NPR',
            'model_year' => 2023,
            'vehicle_type' => 'Buseta',
            'passenger_capacity' => 24,
            'current_mileage' => 15000,
            'status' => 'Activo',
        ]);

        $response->assertRedirect('/vehiculos');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'plate' => $testPlate,
            'internal_number' => '99',
        ]);
    }

    public function test_service_orders_index_and_creation(): void
    {
        $this->actingAs($this->user);

        $client = Client::firstOrCreate(
            ['document_number' => '900999888'],
            [
                'type' => 'Empresa',
                'business_name' => 'Colegio San Jose',
                'status' => 'Activo',
            ]
        );

        $response = $this->get('/ordenes');
        $response->assertStatus(200);
        $response->assertSee('Órdenes de Servicio (ODS)');

        $postResponse = $this->post('/ordenes', [
            'client_id' => $client->id,
            'origin' => 'Bogota Calle 100',
            'destination' => 'Chia Cundinamarca',
            'scheduled_start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'passengers_count' => 15,
            'status' => 'Pendiente',
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('service_orders', [
            'client_id' => $client->id,
            'destination' => 'Chia Cundinamarca',
        ]);
    }

    public function test_fuec_index_renders(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/fuec');
        $response->assertStatus(200);
        $response->assertSee('FUEC Digital (Decreto 1079 / MinTransporte)');
    }

    public function test_drivers_and_maintenance_and_fuel_render(): void
    {
        $this->actingAs($this->user);

        $respConductores = $this->get('/conductores');
        $respConductores->assertStatus(200);
        $respConductores->assertSee('Conductores y Licencias');

        $respMantenimientos = $this->get('/mantenimientos');
        $respMantenimientos->assertStatus(200);
        $respMantenimientos->assertSee('Mantenimiento de Flota');

        $respCombustible = $this->get('/combustible');
        $respCombustible->assertStatus(200);
        $respCombustible->assertSee('Combustible y Rendimiento');
    }

    public function test_wallboard_and_commercial_modules_render(): void
    {
        $this->actingAs($this->user);

        $respMonitoreo = $this->get('/monitoreo');
        $respMonitoreo->assertStatus(200);
        $respMonitoreo->assertSee('Monitoreo GPS');
        $respMonitoreo->assertSee('Wallboard');

        $respClientes = $this->get('/clientes');
        $respClientes->assertStatus(200);
        $respClientes->assertSee('Clientes Corporativos');

        $respContratos = $this->get('/contratos');
        $respContratos->assertStatus(200);
        $respContratos->assertSee('Contratos de Prestación de Servicio');
    }

    public function test_order_status_lifecycle_transitions(): void
    {
        $this->actingAs($this->user);

        $client = Client::firstOrCreate(
            ['document_number' => '900999888-2'],
            ['business_name' => 'Empresa Ciclo E2E', 'type' => 'Empresa', 'email' => 'e2e@empresa.com', 'phone' => '3101234567', 'status' => 'Activo']
        );

        $vehicle = Vehicle::create([
            'plate' => 'E'.rand(10000, 99999),
            'vehicle_type' => 'Van',
            'brand' => 'Renault',
            'line' => 'Master',
            'model_year' => 2024,
            'passenger_capacity' => 16,
            'current_mileage' => 10000.0,
            'soat_expiration' => now()->addMonths(6),
            'technomechanical_expiration' => now()->addMonths(6),
            'contractual_policy_expiration' => now()->addMonths(6),
            'extra_contractual_policy_expiration' => now()->addMonths(6),
            'operation_card_expiration' => now()->addMonths(6),
            'status' => 'Activo',
        ]
        );

        $driver = Employee::firstOrCreate(
            ['document_number' => '10203040'],
            [
                'first_name' => 'Mario',
                'last_name' => 'Conductor E2E',
                'name' => 'Mario Conductor E2E',
                'email' => 'mario@transporte.com',
                'phone' => '3123456789',
                'employee_type' => 'Conductor',
                'license_number' => 'LIC-E2E-100',
                'license_category' => 'C2',
                'driver_license_expiration' => now()->addMonths(8),
                'status' => 'Activo',
            ]
        );

        $orderNum = 'ODS-E2E-'.uniqid();
        $order = ServiceOrder::create([
            'order_number' => $orderNum,
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Terminal Salitre',
            'destination' => 'Aeropuerto El Dorado',
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(3),
            'passengers_count' => 8,
            'status' => 'Asignada',
        ]);

        // Ver detalle de la orden
        $showResp = $this->get("/ordenes/{$order->id}");
        $showResp->assertStatus(200);
        $showResp->assertSee($orderNum);
        $showResp->assertSee('Inspección Preoperacional Hoy');

        // Emitir FUEC
        $fuecResp = $this->post("/ordenes/{$order->id}/emitir-fuec");
        $fuecResp->assertSessionHas('success');
        $this->assertDatabaseHas('fuec_documents', ['service_order_id' => $order->id]);

        // Intento de iniciar sin checklist hoy -> falla con error en sesión
        $failStart = $this->post("/ordenes/{$order->id}/status", [
            'status' => 'En Progreso',
            'mileage' => 10005,
        ]);
        $failStart->assertSessionHas('error');

        // Registrar checklist del día
        PreoperationalChecklist::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'service_order_id' => $order->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'mileage' => 10005,
            'fluid_levels' => ['oil' => true],
            'lights_and_electrical' => ['headlights' => true],
            'tires_and_brakes' => ['brakes' => true],
            'safety_kit' => ['kit' => true],
            'cabin_and_belts' => ['belts' => true],
            'is_approved' => true,
        ]);

        // Ahora iniciar servicio con éxito
        $startResp = $this->post("/ordenes/{$order->id}/status", [
            'status' => 'En Progreso',
            'mileage' => 10005,
        ]);
        $startResp->assertSessionHas('success');
        $this->assertEquals('En Progreso', $order->fresh()->status);
        $this->assertEquals(10005.0, (float) $vehicle->fresh()->current_mileage);

        // Finalizar servicio
        $finishResp = $this->post("/ordenes/{$order->id}/status", [
            'status' => 'Finalizada',
            'mileage' => 10035,
            'service_notes' => 'Servicio concluido a tiempo.',
        ]);
        $finishResp->assertSessionHas('success');
        $this->assertEquals('Finalizada', $order->fresh()->status);
        $this->assertEquals(10035.0, (float) $vehicle->fresh()->current_mileage);
        $this->assertEquals(10035.0, (float) $order->fresh()->end_mileage);
    }
}
