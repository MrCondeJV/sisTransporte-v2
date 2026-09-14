<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\FuelPerformanceCalculator;
use Tests\TestCase;

class FuelAndMaintenanceTest extends TestCase
{
    public function test_maintenance_logging_and_fuel_performance_calculations(): void
    {
        // 1. Inicializar Tenant de prueba
        Tenant::find('test_fase5')?->delete();
        $tenant = Tenant::create([
            'id' => 'test_fase5',
            'name' => 'Transportes Fase 5 S.A.S.',
            'nit' => '900888777-6',
        ]);
        $tenant->domains()->create(['domain' => 'fase5.localhost']);

        tenancy()->initialize($tenant);

        // 2. Crear Vehículo con odómetro inicial en 20,000 km
        $vehicle = Vehicle::create([
            'plate' => 'MNT456',
            'brand' => 'Chevrolet',
            'line' => 'NPR',
            'model_year' => 2024,
            'vehicle_type' => 'Buseta',
            'passenger_capacity' => 28,
            'current_mileage' => 20000,
            'soat_expiration' => now()->addMonths(8),
            'technomechanical_expiration' => now()->addMonths(9),
            'contractual_policy_expiration' => now()->addMonths(6),
            'extra_contractual_policy_expiration' => now()->addMonths(6),
            'operation_card_expiration' => now()->addMonths(12),
            'status' => 'Activo',
        ]);

        // 3. Crear Conductor
        $driver = Employee::create([
            'name' => 'Mario Mantenimiento Conductor',
            'document_number' => '101999888',
            'phone' => '3129990011',
            'email' => 'mario@transporte.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LIC-101999',
            'driver_license_category' => 'C2',
            'driver_license_expiration' => now()->addYear(),
            'status' => 'Activo',
        ]);

        // 4. Test de Mantenimiento Preventivo
        $maintenance = Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => 'Preventivo',
            'maintenance_date' => now()->toDateString(),
            'mileage' => 20500, // 500 km adicionales
            'cost' => 450000,
            'workshop_name' => 'Centro Diesel del Norte',
            'details' => 'Cambio de aceite 15W40 Mobil Delvac, filtro de aire y filtro de combustible.',
            'replaced_parts' => ['Aceite 15W40', 'Filtro Aceite', 'Filtro Combustible'],
        ]);

        $this->assertDatabaseHas('maintenances', [
            'id' => $maintenance->id,
            'cost' => 450000,
            'maintenance_type' => 'Preventivo',
        ]);

        // Simular sincronización de odómetro tras servicio
        if ($maintenance->mileage > $vehicle->current_mileage) {
            $vehicle->update(['current_mileage' => $maintenance->mileage]);
        }
        $this->assertEquals(20500, $vehicle->fresh()->current_mileage);

        // 5. Test de Recargas de Combustible y Rendimiento (km/galón)
        $calculator = app(FuelPerformanceCalculator::class);

        // 5.1 Primer tanqueo (Base inicial en km 20,500)
        $refill1 = FuelRefill::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'refill_date' => now()->toDateString(),
            'gallons' => 15.000,
            'total_cost' => 225000, // $15,000 COP / galón
            'odometer_mileage' => 20500,
            'gas_station_name' => 'Terpel Chía',
        ]);

        $calculator->processAndSync($refill1);
        $refill1->refresh();

        $this->assertEquals(15000, $refill1->price_per_gallon);
        $this->assertNull($refill1->distance_since_last_refill, 'El primer tanqueo no tiene distancia anterior');
        $this->assertNull($refill1->calculated_performance, 'El primer tanqueo no tiene rendimiento previo');

        // 5.2 Segundo tanqueo (Odómetro en km 20,860 -> 360 km recorridos con 12 galones)
        $refill2 = FuelRefill::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'refill_date' => now()->addDays(2)->toDateString(),
            'gallons' => 12.000,
            'total_cost' => 180000,
            'odometer_mileage' => 20860,
            'gas_station_name' => 'Primax Autopista',
        ]);

        $calculator->processAndSync($refill2);
        $refill2->refresh();

        // 360 km / 12 galones = 30 km/galón
        $this->assertEquals(360, (float) $refill2->distance_since_last_refill);
        $this->assertEquals(30.00, (float) $refill2->calculated_performance);
        $this->assertEquals(15000, (float) $refill2->price_per_gallon);

        // El odómetro del vehículo debe sincronizarse al nuevo kilometraje
        $this->assertEquals(20860, (float) $vehicle->fresh()->current_mileage);

        // 6. Test de Auditoría y Detección de Consumo Anómalo
        // Caso: 50 galones para recorrer solo 100 km (2 km/galón -> consumo extremadamente alto o fuga)
        $anomalyMetrics = $calculator->computeRefillMetrics($vehicle, 20960, 50.0, 750000);
        $this->assertTrue($anomalyMetrics['is_anomalous']);
        $this->assertNotNull($anomalyMetrics['warning_message']);
        $this->assertStringContainsString('Posible fuga o sobreconsumo', $anomalyMetrics['warning_message']);

        tenancy()->end();
    }
}
