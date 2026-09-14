<?php

namespace Tests\Feature;

use App\Events\Tenant\GpsLocationReceived;
use App\Models\Tenant;
use App\Models\Tenant\Employee;
use App\Models\Tenant\GpsLocation;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\GpsTelemetryService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GpsTelemetryTest extends TestCase
{
    public function test_gps_telemetry_ingestion_speeding_alerts_and_batch_processing(): void
    {
        Event::fake([GpsLocationReceived::class]);

        // 1. Inicializar Tenant de prueba
        Tenant::find('test_fase6')?->delete();
        $tenant = Tenant::create([
            'id' => 'test_fase6',
            'name' => 'Transportes Telemetría GPS S.A.S.',
            'nit' => '900777666-5',
        ]);
        $tenant->domains()->create(['domain' => 'fase6.localhost']);

        tenancy()->initialize($tenant);

        // 2. Crear Vehículo y Conductor
        $vehicle = Vehicle::create([
            'plate' => 'GPS789',
            'brand' => 'Mercedes-Benz',
            'line' => 'Sprinter 516',
            'model_year' => 2025,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 19,
            'current_mileage' => 10000,
            'soat_expiration' => now()->addYear(),
            'technomechanical_expiration' => now()->addYear(),
            'contractual_policy_expiration' => now()->addYear(),
            'extra_contractual_policy_expiration' => now()->addYear(),
            'operation_card_expiration' => now()->addYear(),
            'status' => 'Activo',
        ]);

        $driver = Employee::create([
            'name' => 'Luis Conductor Monitoreado',
            'document_number' => '102555666',
            'phone' => '3145556677',
            'email' => 'luis@gps.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LIC-102555',
            'driver_license_category' => 'C2',
            'driver_license_expiration' => now()->addYear(),
            'status' => 'Activo',
        ]);

        $service = app(GpsTelemetryService::class);

        // 3. Test de Ingesta Individual Normal (Velocidad normal: 45 km/h)
        $location = $service->recordLocation([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'latitude' => 4.6097100,
            'longitude' => -74.0817500,
            'speed' => 45.0,
            'heading' => 180.0,
            'accuracy' => 4.5,
            'device_timestamp' => now(),
        ]);

        $this->assertDatabaseHas('gps_locations', [
            'id' => $location->id,
            'vehicle_id' => $vehicle->id,
            'speed' => 45.0,
        ]);

        Event::assertDispatched(GpsLocationReceived::class, function ($event) use ($location) {
            return $event->payload['id'] === $location->id &&
                   $event->payload['plate'] === 'GPS789' &&
                   $event->payload['is_speeding'] === false;
        });

        // 4. Test de Alerta por Exceso de Velocidad (> 80 km/h)
        $speedingLocation = $service->recordLocation([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'latitude' => 4.6500000,
            'longitude' => -74.0900000,
            'speed' => 96.5, // Exceso de velocidad
            'heading' => 90.0,
            'accuracy' => 3.2,
            'device_timestamp' => now()->addMinutes(2),
        ]);

        Event::assertDispatched(GpsLocationReceived::class, function ($event) use ($speedingLocation) {
            return $event->payload['id'] === $speedingLocation->id &&
                   $event->payload['speed'] === 96.5 &&
                   $event->payload['is_speeding'] === true;
        });

        // 5. Test de Ingesta Masiva por Lotes (Batch)
        $batch = [];
        $baseLat = 4.6500000;
        $baseLng = -74.0900000;

        for ($i = 1; $i <= 10; $i++) {
            $batch[] = [
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'latitude' => $baseLat + ($i * 0.005),
                'longitude' => $baseLng + ($i * 0.005),
                'speed' => 50 + $i,
                'heading' => 45,
                'accuracy' => 5.0,
                'device_timestamp' => now()->addMinutes($i + 5),
            ];
        }

        $insertedCount = $service->recordBatch($batch);
        $this->assertEquals(10, $insertedCount);
        $this->assertEquals(12, GpsLocation::where('vehicle_id', $vehicle->id)->count());

        // 6. Test de Última Posición de la Flota (getFleetLastPositions)
        $fleetPositions = $service->getFleetLastPositions();
        $this->assertCount(1, $fleetPositions);
        $lastPoint = $fleetPositions->first();
        $this->assertEquals($vehicle->id, $lastPoint->vehicle_id);
        $this->assertEquals(60.0, (float) $lastPoint->speed); // Último punto del batch (50 + 10)

        // 7. Test de Histórico de Ruta Cronológico (getVehicleRouteHistory)
        $routeHistory = $service->getVehicleRouteHistory($vehicle->id);
        $this->assertCount(12, $routeHistory);
        $this->assertTrue($routeHistory->first()->device_timestamp <= $routeHistory->last()->device_timestamp);

        tenancy()->end();
    }
}
