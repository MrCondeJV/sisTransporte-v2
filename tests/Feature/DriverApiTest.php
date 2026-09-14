<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverApiTest extends TestCase
{
    protected Tenant $tenant;

    protected User $driverUser;

    protected Employee $driverEmployee;

    protected Vehicle $vehicle;

    protected Client $client;

    protected ServiceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.identification.central_domains' => ['sistransporte-v2.test', 'localhost']]);

        $this->tenant = Tenant::firstOrCreate(
            ['id' => 'test_fase7'],
            ['company_name' => 'Transportes Fase 7 S.A.S.', 'email' => 'admin@fase7.test']
        );

        if (! $this->tenant->domains()->where('domain', 'test-fase7.sistransporte-v2.test')->exists()) {
            $this->tenant->domains()->create(['domain' => 'test-fase7.sistransporte-v2.test']);
        }

        Artisan::call('tenants:migrate', ['--tenants' => ['test_fase7']]);

        $this->tenant->run(function () {
            $this->driverUser = User::updateOrCreate(
                ['email' => 'conductor7@fase7.test'],
                [
                    'name' => 'Carlos Andrés Conductor',
                    'username' => 'carlos7',
                    'password' => Hash::make('secret123'),
                    'is_active' => true,
                ]
            );

            $this->driverEmployee = Employee::updateOrCreate(
                ['document_number' => '71234567'],
                [
                    'user_id' => $this->driverUser->id,
                    'name' => 'Carlos Andrés Conductor',
                    'phone' => '3007654321',
                    'email' => 'conductor7@fase7.test',
                    'employee_type' => 'Conductor',
                    'contract_number' => 'CTR-COND-007',
                    'contract_type' => 'Termino Fijo',
                    'contract_start_date' => now()->subMonths(6),
                    'contract_end_date' => now()->addMonths(6),
                    'driver_license_number' => 'LC-778899',
                    'driver_license_category' => 'C2',
                    'driver_license_expiration' => now()->addMonths(10),
                    'status' => 'Activo',
                ]
            );

            $this->vehicle = Vehicle::updateOrCreate(
                ['plate' => 'KMY700'],
                [
                    'internal_number' => 'BUS-700',
                    'vehicle_type' => 'Microbus',
                    'brand' => 'Mercedes-Benz',
                    'line' => 'Sprinter',
                    'model_year' => 2024,
                    'passenger_capacity' => 19,
                    'current_mileage' => 25000.0,
                    'soat_expiration' => now()->addMonths(8),
                    'technomechanical_expiration' => now()->addMonths(7),
                    'contractual_policy_expiration' => now()->addMonths(9),
                    'extra_contractual_policy_expiration' => now()->addMonths(9),
                    'operation_card_expiration' => now()->addMonths(11),
                    'status' => 'Activo',
                ]
            );

            $this->client = Client::firstOrCreate(
                ['document_number' => '900777000-1'],
                [
                    'type' => 'Empresa',
                    'business_name' => 'Colegio Bilingüe del Norte',
                    'email' => 'rectoria@colegionorte.edu.co',
                    'phone' => '6017770000',
                    'address' => 'Cl 170 # 15-20',
                    'status' => 'Activo',
                ]
            );

            $this->order = ServiceOrder::updateOrCreate(
                ['order_number' => 'OS-2026-F7-001'],
                [
                    'client_id' => $this->client->id,
                    'vehicle_id' => $this->vehicle->id,
                    'driver_id' => $this->driverEmployee->id,
                    'origin' => 'Colegio del Norte',
                    'destination' => 'Parque Jaime Duque',
                    'route_name' => 'Salida Pedagógica',
                    'scheduled_start_time' => now()->addHour(),
                    'scheduled_end_time' => now()->addHours(6),
                    'passengers_count' => 18,
                    'status' => 'Asignada',
                ]
            );
        });
    }

    public function test_api_requires_tenant_identification(): void
    {
        $response = $this->postJson('/api/v1/driver/login', [
            'email' => 'conductor7@fase7.test',
            'password' => 'secret123',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['error', 'message']);
    }

    public function test_driver_login_with_valid_credentials_returns_sanctum_token(): string
    {
        $response = $this->withHeaders(['X-Tenant' => 'test_fase7'])
            ->postJson('/api/v1/driver/login', [
                'email' => 'conductor7@fase7.test',
                'password' => 'secret123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
                'driver' => ['id', 'name', 'driver_license_number', 'license_status'],
            ]);

        $this->assertEquals('Al Día', $response->json('driver.license_status'));

        return $response->json('token');
    }

    public function test_driver_login_with_invalid_credentials_is_rejected(): void
    {
        $response = $this->withHeaders(['X-Tenant' => 'test_fase7'])
            ->postJson('/api/v1/driver/login', [
                'email' => 'conductor7@fase7.test',
                'password' => 'wrong_password',
            ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_authenticated_driver_can_get_profile(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/driver/profile');

        $response->assertStatus(200)
            ->assertJsonPath('driver.name', 'Carlos Andrés Conductor')
            ->assertJsonPath('driver.is_eligible', true);
    }

    public function test_driver_can_list_assigned_orders(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/driver/orders');

        $response->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('orders.0.order_number', 'OS-2026-F7-001')
            ->assertJsonPath('orders.0.origin', 'Colegio del Norte');
    }

    public function test_driver_submits_preoperational_checklist(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/checklist', [
            'vehicle_id' => $this->vehicle->id,
            'service_order_id' => $this->order->id,
            'mileage' => 25050.0,
            'is_approved' => true,
            'fluid_levels' => ['oil' => 'ok', 'coolant' => 'ok', 'brakes' => 'ok'],
            'lights_and_electrical' => ['headlights' => 'ok', 'turn_signals' => 'ok', 'horn' => 'ok'],
            'tires_and_brakes' => ['pressure' => 'ok', 'tread' => 'ok', 'brakes' => 'ok'],
            'safety_kit' => ['extinguisher' => 'ok', 'first_aid' => 'ok', 'cones' => 'ok'],
            'cabin_and_belts' => ['belts' => 'ok', 'mirrors' => 'ok'],
            'observations' => 'Vehículo en excelente estado para iniciar ruta.',
            'driver_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('checklist.is_approved', true);
        $this->assertEquals(25050.0, (float) $response->json('checklist.mileage'));

        // Odómetro actualizado
        $this->tenant->run(function () {
            $this->assertEquals(25050.0, $this->vehicle->fresh()->current_mileage);
        });
    }

    public function test_order_cannot_start_without_approved_checklist(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        // En un nuevo día o sin checklist
        $this->tenant->run(function () {
            PreoperationalChecklist::where('vehicle_id', $this->vehicle->id)->delete();
        });

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson("/api/v1/driver/orders/{$this->order->id}/status", [
            'status' => 'En Progreso',
            'mileage' => 25050,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'message', 'errors']);
    }

    public function test_order_status_advances_after_checklist(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        // 1. Enviar checklist aprobado
        $this->test_driver_submits_preoperational_checklist();

        // 2. Iniciar orden -> En Progreso
        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson("/api/v1/driver/orders/{$this->order->id}/status", [
            'status' => 'En Progreso',
            'mileage' => 25050,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('order.status', 'En Progreso');
        $this->assertEquals(25050.0, (float) $response->json('order.start_mileage'));

        // 3. Finalizar orden -> Finalizada
        $responseFinish = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson("/api/v1/driver/orders/{$this->order->id}/status", [
            'status' => 'Finalizada',
            'mileage' => 25180,
            'notes' => 'Servicio terminado sin contratiempos.',
        ]);

        $responseFinish->assertStatus(200)
            ->assertJsonPath('order.status', 'Finalizada');
        $this->assertEquals(25180.0, (float) $responseFinish->json('order.end_mileage'));

        $this->tenant->run(function () {
            $this->assertEquals(25180.0, $this->vehicle->fresh()->current_mileage);
        });
    }

    public function test_driver_can_report_incident(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/incidents', [
            'vehicle_id' => $this->vehicle->id,
            'service_order_id' => $this->order->id,
            'incident_type' => 'Trafico',
            'description' => 'Cierre vial temporal en Autopista Norte por mantenimiento de calzada.',
            'photos' => ['foto_trafico_1.jpg'],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('incident.incident_type', 'Trafico')
            ->assertJsonPath('incident.status', 'Abierta');

        $this->tenant->run(function () {
            $this->assertDatabaseHas('service_incidents', [
                'vehicle_id' => $this->vehicle->id,
                'incident_type' => 'Trafico',
            ]);
        });
    }

    public function test_driver_records_fuel_refill_with_performance(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        // Primera recarga
        $this->tenant->run(function () {
            FuelRefill::create([
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => $this->driverEmployee->id,
                'refill_date' => now()->subDays(2)->toDateString(),
                'gallons' => 15.0,
                'total_cost' => 150000.0,
                'price_per_gallon' => 10000.0,
                'odometer_mileage' => 25000.0,
            ]);
        });

        // Segunda recarga vía API móvil
        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/fuel', [
            'vehicle_id' => $this->vehicle->id,
            'service_order_id' => $this->order->id,
            'gallons' => 8.0,
            'total_cost' => 88000.0,
            'odometer_mileage' => 25240.0,
            'gas_station_name' => 'Terpel Autonorte Km 22',
            'notes' => 'Tanque lleno',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('performance_metrics.distance_km', 240)
            ->assertJsonPath('performance_metrics.km_per_gallon', 30);
        $this->assertEquals(25240.0, (float) $response->json('fuel_refill.odometer_mileage'));

        $this->tenant->run(function () {
            $this->assertEquals(25240.0, $this->vehicle->fresh()->current_mileage);
        });
    }

    public function test_driver_sends_gps_telemetry_ping_and_batch(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        // 1. Single Ping
        $responsePing = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/telemetry/ping', [
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 4.7110,
            'longitude' => -74.0721,
            'speed' => 45.5,
            'heading' => 180.0,
            'altitude' => 2600.0,
        ]);

        $responsePing->assertStatus(201);
        $this->assertEquals(4.711, (float) $responsePing->json('location.latitude'));
        $this->assertEquals(-74.0721, (float) $responsePing->json('location.longitude'));

        // 2. Batch from offline buffer
        $responseBatch = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/telemetry/batch', [
            'locations' => [
                [
                    'vehicle_id' => $this->vehicle->id,
                    'latitude' => 4.7120,
                    'longitude' => -74.0730,
                    'speed' => 50.0,
                    'heading' => 182.0,
                ],
                [
                    'vehicle_id' => $this->vehicle->id,
                    'latitude' => 4.7135,
                    'longitude' => -74.0742,
                    'speed' => 52.0,
                    'heading' => 185.0,
                ],
            ],
        ]);

        $responseBatch->assertStatus(201)
            ->assertJsonPath('inserted_count', 2);
    }

    public function test_driver_can_logout_and_revoke_token(): void
    {
        $token = $this->test_driver_login_with_valid_credentials_returns_sanctum_token();

        $response = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/driver/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Sesión cerrada exitosamente.');

        // Token ya no debe funcionar (limpiar caché en memoria de guard en entorno de prueba)
        Auth::forgetGuards();

        $responseProfile = $this->withHeaders([
            'X-Tenant' => 'test_fase7',
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/driver/profile');

        $responseProfile->assertStatus(401);
    }
}
