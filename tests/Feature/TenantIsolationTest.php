<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Vehicle;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    public function test_tenants_have_isolated_databases(): void
    {
        // 1. Limpiar tenants previos de prueba si existen
        Tenant::find('test_empresa_a')?->delete();
        Tenant::find('test_empresa_b')?->delete();

        // 2. Crear Tenant A y Tenant B
        $tenantA = Tenant::create([
            'id' => 'test_empresa_a',
            'name' => 'Empresa Alfa S.A.S.',
            'nit' => '800111222-1',
        ]);
        $tenantA->domains()->create(['domain' => 'alfa.localhost']);

        $tenantB = Tenant::create([
            'id' => 'test_empresa_b',
            'name' => 'Empresa Beta S.A.S.',
            'nit' => '900333444-2',
        ]);
        $tenantB->domains()->create(['domain' => 'beta.localhost']);

        // 3. Ejecutar contexto en Tenant A y crear un vehículo
        tenancy()->initialize($tenantA);

        Vehicle::create([
            'plate' => 'ABC123',
            'brand' => 'Mercedes-Benz',
            'line' => 'Sprinter',
            'model_year' => 2024,
            'passenger_capacity' => 19,
            'current_mileage' => 15000,
        ]);

        $this->assertDatabaseHas('vehicles', ['plate' => 'ABC123']);
        $this->assertEquals(1, Vehicle::count());

        tenancy()->end();

        // 4. Cambiar contexto a Tenant B y verificar aislamiento
        tenancy()->initialize($tenantB);

        $this->assertDatabaseMissing('vehicles', ['plate' => 'ABC123']);
        $this->assertEquals(0, Vehicle::count(), 'Tenant B no debe ver los vehículos de Tenant A');

        tenancy()->end();

        // Limpieza
        $tenantA->delete();
        $tenantB->delete();
    }
}
