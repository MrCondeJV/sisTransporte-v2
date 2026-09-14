<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use App\Models\Tenant\Vehicle;
use App\Services\Etl\LegacyDataMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LegacyMigrationTest extends TestCase
{
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['id' => 'test_fase8'],
            ['company_name' => 'Transportes Fase 8 S.A.S.', 'email' => 'admin@fase8.test']
        );

        if (! $this->tenant->domains()->where('domain', 'test-fase8.sistransporte-v2.test')->exists()) {
            $this->tenant->domains()->create(['domain' => 'test-fase8.sistransporte-v2.test']);
        }

        Artisan::call('tenants:migrate', ['--tenants' => ['test_fase8']]);
    }

    public function test_etl_migration_service_imports_data_correctly(): void
    {
        $legacyData = [
            'partners' => [
                [
                    'id' => 10,
                    'nombre' => 'Aliado Logístico del Oriente',
                    'nit' => '900555444-1',
                    'telefono' => '3101112233',
                    'correo' => 'contacto@aliadooriente.com',
                ],
                [
                    'id' => 11,
                    'nombre' => '', // Sin nombre -> debe omitirse
                    'nit' => '900999888-2',
                ],
            ],
            'clients' => [
                [
                    'id' => 20,
                    'tipo_cliente' => 'empresa',
                    'razon_social' => 'Ecopetrol Refinería',
                    'nit' => '899999068-1',
                    'telefono' => '6012345678',
                    'correo' => 'transporte@ecopetrol.com.co',
                ],
                [
                    'id' => 21,
                    'tipo_cliente' => 'persona',
                    'nombres' => 'María Camila',
                    'apellidos' => 'Suárez Rodríguez',
                    'cedula' => '52123456',
                    'telefono' => '3009876543',
                    'correo' => 'maria.suarez@email.test',
                ],
            ],
            'vehicles' => [
                [
                    'id' => 30,
                    'placa' => 'wre123',
                    'NumeroInterno' => 'BUS-030',
                    'Marca' => 'Chevrolet',
                    'Linea' => 'NPR',
                    'Modelo' => 2023,
                    'Color' => 'Blanco',
                    'TipoVehiculo' => 'Buseta',
                    'Capacidad' => 24,
                    'KilometrajeActual' => 48200.50,
                    'proveedor_id' => 10,
                    'FechaVencimientoSoat' => '2027-02-15',
                    'FechaVencimientoTecno' => '2027-01-20',
                    'FechaVencimientoPoliza' => '2027-04-10',
                ],
                [
                    'id' => 31,
                    'placa' => '', // Placa vacía -> debe omitirse
                    'NumeroInterno' => 'BUS-031',
                ],
            ],
            'employees' => [
                [
                    'id' => 40,
                    'NumeroDocumento' => '80123456',
                    'Nombres' => 'Jorge Alberto',
                    'Apellidos' => 'Ramírez Peña',
                    'Cargo' => 'Conductor de Operaciones',
                    'Telefono' => '3123456789',
                    'Correo' => 'jorge.ramirez@empresa.com',
                    'NumeroLicencia' => 'LC-80123456',
                    'CategoriaLicencia' => 'C2',
                    'FechaVencimientoLicencia' => '2028-06-30',
                    'proveedor_id' => 10,
                ],
                [
                    'id' => 41,
                    'NumeroDocumento' => '1020304050',
                    'Nombres' => 'Diana Marcela',
                    'Apellidos' => 'Gómez',
                    'Cargo' => 'Coordinadora de Rutas',
                    'Correo' => 'diana.gomez@empresa.com',
                ],
            ],
            'contracts' => [
                [
                    'id' => 50,
                    'NumeroContrato' => 'CTR-ECOPETROL-2026',
                    'cliente_id' => 20,
                    'Objeto' => 'Rutas corporativas refinería',
                    'FechaInicio' => '2026-01-01',
                    'FechaFin' => '2026-12-31',
                    'Valor' => 150000000.00,
                ],
            ],
        ];

        $service = new LegacyDataMigrationService;
        $result = $service->migrate(
            tenant: $this->tenant,
            dryRun: false,
            connectionName: null,
            customData: $legacyData
        );

        $this->assertEquals('success', $result['status']);
        $this->assertFalse($result['dry_run']);

        // Aserciones de conteo de entidades
        $this->assertEquals(2, $result['counts']['partners']['extracted']);
        $this->assertEquals(1, $result['counts']['partners']['imported']);
        $this->assertEquals(1, $result['counts']['partners']['skipped']);

        $this->assertEquals(2, $result['counts']['clients']['extracted']);
        $this->assertEquals(2, $result['counts']['clients']['imported']);
        $this->assertEquals(0, $result['counts']['clients']['skipped']);

        $this->assertEquals(2, $result['counts']['vehicles']['extracted']);
        $this->assertEquals(1, $result['counts']['vehicles']['imported']);
        $this->assertEquals(1, $result['counts']['vehicles']['skipped']);

        $this->assertEquals(2, $result['counts']['employees']['extracted']);
        $this->assertEquals(2, $result['counts']['employees']['imported']);

        $this->assertEquals(1, $result['counts']['contracts']['extracted']);
        $this->assertEquals(1, $result['counts']['contracts']['imported']);

        // Verificar datos en la base del tenant
        $this->tenant->run(function () {
            // Partner
            $partner = Partner::where('nit', '900555444-1')->first();
            $this->assertNotNull($partner);
            $this->assertEquals('Aliado Logístico del Oriente', $partner->name);

            // Clientes
            $empresa = Client::where('document_number', '899999068-1')->first();
            $this->assertNotNull($empresa);
            $this->assertEquals('Empresa', $empresa->type);
            $this->assertEquals('Ecopetrol Refinería', $empresa->business_name);

            $persona = Client::where('document_number', '52123456')->first();
            $this->assertNotNull($persona);
            $this->assertEquals('Persona Natural', $persona->type);
            $this->assertEquals('María Camila', $persona->first_name);

            // Vehículo
            $vehicle = Vehicle::where('plate', 'WRE123')->first();
            $this->assertNotNull($vehicle);
            $this->assertEquals('WRE123', $vehicle->plate);
            $this->assertEquals('Chevrolet', $vehicle->brand);
            $this->assertEquals(48200.50, $vehicle->current_mileage);
            $this->assertEquals($partner->id, $vehicle->partner_id);

            // Empleado Conductor vinculado a Usuario
            $conductor = Employee::where('document_number', '80123456')->first();
            $this->assertNotNull($conductor);
            $this->assertEquals('Conductor', $conductor->employee_type);
            $this->assertNotNull($conductor->user_id);
            $this->assertDatabaseHas('users', ['id' => $conductor->user_id, 'email' => 'jorge.ramirez@empresa.com']);

            // Contrato vinculado al cliente
            $contract = Contract::where('contract_number', 'CTR-ECOPETROL-2026')->first();
            $this->assertNotNull($contract);
            $this->assertEquals($empresa->id, $contract->client_id);
            $this->assertEquals(150000000.0, (float) $contract->value);
        });
    }

    public function test_etl_migration_dry_run_does_not_persist_data(): void
    {
        $legacyData = [
            'partners' => [
                ['id' => 99, 'nombre' => 'Aliado Falso DryRun', 'nit' => '999111222-3'],
            ],
            'clients' => [
                ['id' => 99, 'tipo_cliente' => 'empresa', 'razon_social' => 'Cliente Falso DryRun', 'nit' => '999888777-1'],
            ],
            'vehicles' => [
                ['id' => 99, 'placa' => 'DRY999', 'Marca' => 'Hino', 'Modelo' => 2025],
            ],
            'employees' => [],
            'contracts' => [],
        ];

        $service = new LegacyDataMigrationService;
        $result = $service->migrate(
            tenant: $this->tenant,
            dryRun: true,
            connectionName: null,
            customData: $legacyData
        );

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['dry_run']);
        $this->assertEquals(1, $result['counts']['partners']['imported']);
        $this->assertEquals(1, $result['counts']['vehicles']['imported']);

        // Verificar que en dry-run NO se insertaron registros
        $this->tenant->run(function () {
            $this->assertDatabaseMissing('partners', ['nit' => '999111222-3']);
            $this->assertDatabaseMissing('vehicles', ['plate' => 'DRY999']);
        });
    }

    public function test_artisan_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('migrate:legacy-data', [
            '--tenant' => 'test_fase8',
            '--dry-run' => true,
        ]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
