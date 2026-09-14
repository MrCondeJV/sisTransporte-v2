<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use App\Models\Tenant\Vehicle;
use Tests\TestCase;

class FleetAndPersonnelTest extends TestCase
{
    public function test_fleet_and_personnel_entities_and_document_alerts(): void
    {
        // 1. Crear tenant de prueba
        Tenant::find('test_fase2')?->delete();
        $tenant = Tenant::create([
            'id' => 'test_fase2',
            'name' => 'Transportes Fase 2 S.A.S.',
            'nit' => '900555666-1',
        ]);
        $tenant->domains()->create(['domain' => 'fase2.localhost']);

        tenancy()->initialize($tenant);

        // 2. Crear Aliado
        $partner = Partner::create([
            'name' => 'Aliados del Valle S.A.S.',
            'nit' => '800999000-1',
            'phone' => '3201112233',
            'email' => 'aliado@valle.com',
            'status' => 'Activo',
        ]);
        $this->assertDatabaseHas('partners', ['name' => 'Aliados del Valle S.A.S.']);

        // 3. Crear Conductor
        $driver = Employee::create([
            'name' => 'Carlos Conductor Experto',
            'document_number' => '1020304050',
            'phone' => '3157778899',
            'email' => 'carlos@empresa.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LC-998877',
            'driver_license_category' => 'C2',
            'driver_license_expiration' => now()->addMonths(6),
            'partner_id' => $partner->id,
            'status' => 'Activo',
        ]);
        $this->assertDatabaseHas('employees', ['document_number' => '1020304050']);
        $this->assertEquals($partner->id, $driver->partner->id);

        // 4. Crear Cliente y Contrato
        $client = Client::create([
            'type' => 'Empresa',
            'business_name' => 'Ecopetrol S.A.',
            'document_number' => '899999068-1',
            'email' => 'transporte@ecopetrol.com.co',
            'status' => 'Activo',
        ]);
        $this->assertDatabaseHas('clients', ['business_name' => 'Ecopetrol S.A.']);

        $contract = Contract::create([
            'client_id' => $client->id,
            'contract_number' => 'CONT-2026-001',
            'contract_object' => 'Transporte de personal administrativo y operativo ruta Bogotá - Barrancabermeja',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'value' => 150000000,
            'contract_type' => 'Empresarial',
            'status' => 'Vigente',
        ]);
        $this->assertDatabaseHas('contracts', ['contract_number' => 'CONT-2026-001']);
        $this->assertEquals($client->id, $contract->client->id);

        // 5. Crear Vehículo con Documentos al Día
        $vehicleOk = Vehicle::create([
            'plate' => 'UVR456',
            'brand' => 'Chevrolet',
            'line' => 'NKR',
            'model_year' => 2025,
            'vehicle_type' => 'Buseta',
            'passenger_capacity' => 24,
            'current_mileage' => 5000,
            'soat_expiration' => now()->addMonths(6),
            'technomechanical_expiration' => now()->addMonths(8),
            'contractual_policy_expiration' => now()->addMonths(5),
            'extra_contractual_policy_expiration' => now()->addMonths(5),
            'operation_card_expiration' => now()->addMonths(10),
            'partner_id' => $partner->id,
            'default_driver_id' => $driver->id,
            'status' => 'Activo',
        ]);
        $this->assertEquals('Al Día', $vehicleOk->document_status);
        $this->assertEquals('success', $vehicleOk->document_status_color);

        // 6. Probar Vehículo con Documento Vencido (SOAT vencido ayer)
        $vehicleExpired = Vehicle::create([
            'plate' => 'EXP123',
            'brand' => 'Renault',
            'line' => 'Master',
            'model_year' => 2020,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 16,
            'soat_expiration' => now()->subDay(),
            'technomechanical_expiration' => now()->addMonths(4),
            'contractual_policy_expiration' => now()->addMonths(4),
            'extra_contractual_policy_expiration' => now()->addMonths(4),
            'operation_card_expiration' => now()->addMonths(4),
            'status' => 'Activo',
        ]);
        $this->assertEquals('Vencido', $vehicleExpired->document_status);
        $this->assertEquals('danger', $vehicleExpired->document_status_color);

        // 7. Probar Vehículo con Documento Por Vencer (vence en 15 días)
        $vehicleWarning = Vehicle::create([
            'plate' => 'WRN789',
            'brand' => 'Nissan',
            'line' => 'Urvan',
            'model_year' => 2022,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 15,
            'soat_expiration' => now()->addDays(15),
            'technomechanical_expiration' => now()->addMonths(4),
            'contractual_policy_expiration' => now()->addMonths(4),
            'extra_contractual_policy_expiration' => now()->addMonths(4),
            'operation_card_expiration' => now()->addMonths(4),
            'status' => 'Activo',
        ]);
        $this->assertEquals('Por Vencer', $vehicleWarning->document_status);
        $this->assertEquals('warning', $vehicleWarning->document_status_color);

        tenancy()->end();

        // Limpieza
        $tenant->delete();
    }
}
