<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OperationalQualityAssuranceTest extends TestCase
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
     * Prueba de Seguridad: Un usuario operador de tenant NO puede ingresar al panel central /admin.
     */
    public function test_tenant_operator_is_forbidden_from_central_admin_panel(): void
    {
        $response = $this->actingAs($this->tenantUser)->get('/admin');
        // Debe ser 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Límite Normativo: No se puede emitir FUEC sin asignar conductor y vehículo aptos.
     */
    public function test_cannot_emit_fuec_without_vehicle_and_driver(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900111222-3'],
            ['business_name' => 'Cliente Sin Asignar', 'type' => 'Empresa', 'email' => 'sin@empresa.com', 'phone' => '3001234567', 'status' => 'Activo']
        );

        $order = ServiceOrder::create([
            'order_number' => 'ODS-QA-SIN-'.uniqid(),
            'client_id' => $client->id,
            'origin' => 'Bogota',
            'destination' => 'Medellin',
            'scheduled_start_time' => now()->addDay(),
            'scheduled_end_time' => now()->addDays(2),
            'passengers_count' => 4,
            'status' => 'Pendiente',
        ]);

        $response = $this->post("/ordenes/{$order->id}/emitir-fuec");
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('fuec_documents', ['service_order_id' => $order->id]);
    }

    /**
     * Límite Normativo: No se puede iniciar viaje si el vehículo tiene documentos vencidos.
     */
    public function test_cannot_start_service_with_expired_vehicle_documents(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900333444-5'],
            ['business_name' => 'Cliente Doc Vencido', 'type' => 'Empresa', 'email' => 'vencido@empresa.com', 'phone' => '3009876543', 'status' => 'Activo']
        );

        $expiredVehicle = Vehicle::create([
            'plate' => 'VN'.rand(1000, 9999),
            'vehicle_type' => 'Buseta',
            'brand' => 'Chevrolet',
            'line' => 'NPR',
            'model_year' => 2020,
            'passenger_capacity' => 24,
            'current_mileage' => 80000.0,
            'soat_expiration' => now()->subDays(5), // ¡SOAT VENCIDO!
            'technomechanical_expiration' => now()->addMonths(6),
            'operation_card_expiration' => now()->addMonths(6),
            'status' => 'Activo',
        ]);

        $driver = Employee::firstOrCreate(
            ['document_number' => '50607080'],
            [
                'name' => 'Conductor Prueba QA',
                'email' => 'qa_cond@empresa.com',
                'phone' => '3112223344',
                'employee_type' => 'Conductor',
                'driver_license_number' => 'LIC-QA-999',
                'driver_license_expiration' => now()->addMonths(12),
                'status' => 'Activo',
            ]
        );

        $order = ServiceOrder::create([
            'order_number' => 'ODS-QA-VNC-'.uniqid(),
            'client_id' => $client->id,
            'vehicle_id' => $expiredVehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Cali',
            'destination' => 'Popayan',
            'scheduled_start_time' => now()->addHours(2),
            'scheduled_end_time' => now()->addHours(6),
            'passengers_count' => 20,
            'status' => 'Asignada',
        ]);

        $response = $this->post("/ordenes/{$order->id}/status", [
            'status' => 'En Progreso',
            'mileage' => 80000,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('Asignada', $order->fresh()->status);
    }

    /**
     * Registro de Combustible: Comprueba cálculo y sincronización de odómetro.
     */
    public function test_fuel_refill_records_and_updates_vehicle_mileage(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'GAS101'],
            [
                'vehicle_type' => 'Van',
                'brand' => 'Nissan',
                'line' => 'Urvan',
                'model_year' => 2023,
                'passenger_capacity' => 15,
                'current_mileage' => 30000.0,
                'soat_expiration' => now()->addMonths(6),
                'technomechanical_expiration' => now()->addMonths(6),
                'status' => 'Activo',
            ]
        );

        $response = $this->post('/combustible', [
            'vehicle_id' => $vehicle->id,
            'refill_date' => now()->toDateString(),
            'gallons' => 12.5,
            'total_cost' => 206250.0,
            'odometer_mileage' => 30250.0,
            'gas_station_name' => 'Terpel Norte',
            'fuel_type' => 'Diesel',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(30250.0, (float) $vehicle->fresh()->current_mileage);
        $this->assertDatabaseHas('fuel_refills', [
            'vehicle_id' => $vehicle->id,
            'gas_station_name' => 'Terpel Norte',
        ]);
    }

    /**
     * Registro de Mantenimiento Preventivo / Correctivo.
     */
    public function test_maintenance_record_creation(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'MTO202'],
            [
                'vehicle_type' => 'Van',
                'brand' => 'Toyota',
                'line' => 'Hiace',
                'model_year' => 2022,
                'passenger_capacity' => 12,
                'current_mileage' => 45000.0,
                'soat_expiration' => now()->addMonths(6),
                'technomechanical_expiration' => now()->addMonths(6),
                'status' => 'Activo',
            ]
        );

        $response = $this->post('/mantenimientos', [
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => 'Preventivo',
            'maintenance_date' => now()->toDateString(),
            'mileage' => 45000.0,
            'cost' => 350000.0,
            'workshop_name' => 'Taller Central Especializado',
            'details' => 'Cambio de aceite 10W40, filtro de aceite y filtro de aire.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('maintenances', [
            'vehicle_id' => $vehicle->id,
            'workshop_name' => 'Taller Central Especializado',
        ]);
    }
}
