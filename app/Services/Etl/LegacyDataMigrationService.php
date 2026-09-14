<?php

namespace App\Services\Etl;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LegacyDataMigrationService
{
    protected array $partnerMap = [];

    protected array $clientMap = [];

    protected array $vehicleMap = [];

    protected array $employeeMap = [];

    protected array $warnings = [];

    protected array $skipped = [];

    protected array $counts = [
        'partners' => ['extracted' => 0, 'imported' => 0, 'skipped' => 0],
        'clients' => ['extracted' => 0, 'imported' => 0, 'skipped' => 0],
        'vehicles' => ['extracted' => 0, 'imported' => 0, 'skipped' => 0],
        'employees' => ['extracted' => 0, 'imported' => 0, 'skipped' => 0],
        'contracts' => ['extracted' => 0, 'imported' => 0, 'skipped' => 0],
    ];

    /**
     * Ejecuta el proceso de extracción, transformación y carga (ETL).
     */
    public function migrate(
        Tenant $tenant,
        bool $dryRun = false,
        ?string $connectionName = 'legacy',
        array $customData = [],
        ?callable $onProgress = null
    ): array {
        $startTime = microtime(true);

        $tenant->run(function () use ($dryRun, $connectionName, $customData, $onProgress) {
            // 1. Migrar Proveedores y Aliados (Partners)
            $this->notify($onProgress, 'Migrando aliados y proveedores...');
            $partnersData = $customData['partners'] ?? $this->fetchLegacyTable($connectionName, 'aliados') ?? [];
            if (empty($partnersData) && empty($customData)) {
                $partnersData = $this->fetchLegacyTable($connectionName, 'proveedores') ?? [];
            }
            $this->migratePartners($partnersData, $dryRun);

            // 2. Migrar Clientes
            $this->notify($onProgress, 'Migrando clientes...');
            $clientsData = $customData['clients'] ?? $this->fetchLegacyTable($connectionName, 'clientes') ?? [];
            $this->migrateClients($clientsData, $dryRun);

            // 3. Migrar Vehículos
            $this->notify($onProgress, 'Migrando vehículos y flota...');
            $vehiclesData = $customData['vehicles'] ?? $this->fetchLegacyTable($connectionName, 'vehiculos') ?? [];
            $this->migrateVehicles($vehiclesData, $dryRun);

            // 4. Migrar Empleados y Conductores
            $this->notify($onProgress, 'Migrando personal y conductores...');
            $employeesData = $customData['employees'] ?? $this->fetchLegacyTable($connectionName, 'empleados') ?? [];
            $this->migrateEmployees($employeesData, $dryRun);

            // 5. Migrar Contratos
            $this->notify($onProgress, 'Migrando contratos...');
            $contractsData = $customData['contracts'] ?? $this->fetchLegacyTable($connectionName, 'contratos') ?? [];
            $this->migrateContracts($contractsData, $dryRun);
        });

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'status' => 'success',
            'dry_run' => $dryRun,
            'tenant_id' => $tenant->id,
            'counts' => $this->counts,
            'warnings' => $this->warnings,
            'skipped' => $this->skipped,
            'duration_seconds' => $duration,
        ];
    }

    protected function migratePartners(array $rows, bool $dryRun): void
    {
        $this->counts['partners']['extracted'] = count($rows);

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? $row['ID'] ?? null;
            $name = trim($row['nombre'] ?? $row['razon_social'] ?? $row['Nombre'] ?? '');
            $doc = trim($row['nit'] ?? $row['documento'] ?? $row['cedula'] ?? $row['NIT'] ?? '900000000-'.$oldId);

            if (empty($name)) {
                $this->counts['partners']['skipped']++;
                $this->skipped[] = "Aliado ID #{$oldId}: omitido por no tener nombre/razón social.";

                continue;
            }

            if (! $dryRun) {
                $partner = Partner::firstOrCreate(
                    ['nit' => $doc],
                    [
                        'name' => $name,
                        'phone' => $row['telefono'] ?? $row['Telefono'] ?? null,
                        'email' => $row['correo'] ?? $row['email'] ?? $row['Correo'] ?? null,
                        'address' => $row['direccion'] ?? $row['Direccion'] ?? null,
                        'status' => 'Activo',
                    ]
                );
                if ($oldId) {
                    $this->partnerMap[$oldId] = $partner->id;
                }
            }

            $this->counts['partners']['imported']++;
        }
    }

    protected function migrateClients(array $rows, bool $dryRun): void
    {
        $this->counts['clients']['extracted'] = count($rows);

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? $row['ID'] ?? null;
            $name = trim($row['razon_social'] ?? $row['nombre'] ?? $row['Nombre'] ?? '');
            $doc = trim($row['nit'] ?? $row['cedula'] ?? $row['documento'] ?? $row['NIT'] ?? '900100000-'.$oldId);
            $type = (isset($row['tipo_cliente']) && str_contains(strtolower($row['tipo_cliente']), 'persona')) ? 'Persona Natural' : 'Empresa';

            if (empty($name) && empty($row['nombres'])) {
                $this->counts['clients']['skipped']++;
                $this->skipped[] = "Cliente ID #{$oldId}: omitido por no tener nombre.";

                continue;
            }

            if (! $dryRun) {
                $client = Client::firstOrCreate(
                    ['document_number' => $doc],
                    [
                        'type' => $type,
                        'business_name' => $type === 'Empresa' ? ($name ?: 'Cliente Empresa') : null,
                        'first_name' => $type === 'Persona Natural' ? ($row['nombres'] ?? $name) : null,
                        'last_name' => $type === 'Persona Natural' ? ($row['apellidos'] ?? null) : null,
                        'phone' => $row['telefono'] ?? $row['Telefono'] ?? null,
                        'email' => $row['correo'] ?? $row['email'] ?? $row['Correo'] ?? null,
                        'address' => $row['direccion'] ?? $row['Direccion'] ?? null,
                        'status' => 'Activo',
                    ]
                );
                if ($oldId) {
                    $this->clientMap[$oldId] = $client->id;
                }
            }

            $this->counts['clients']['imported']++;
        }
    }

    protected function migrateVehicles(array $rows, bool $dryRun): void
    {
        $this->counts['vehicles']['extracted'] = count($rows);

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? $row['ID'] ?? null;
            $rawPlate = $row['Placa'] ?? $row['placa'] ?? '';
            $plate = strtoupper(trim($rawPlate));

            if (empty($plate) || strlen($plate) < 5) {
                $this->counts['vehicles']['skipped']++;
                $this->skipped[] = "Vehículo ID #{$oldId}: placa inválida o vacía ('{$rawPlate}').";

                continue;
            }

            $oldPartnerId = $row['proveedor_id'] ?? $row['aliadoid'] ?? null;
            $partnerId = $oldPartnerId ? ($this->partnerMap[$oldPartnerId] ?? null) : null;

            if (! $dryRun) {
                $vehicle = Vehicle::updateOrCreate(
                    ['plate' => $plate],
                    [
                        'internal_number' => $row['NumeroInterno'] ?? $row['numero_interno'] ?? 'BUS-'.$oldId,
                        'brand' => $row['Marca'] ?? $row['marca'] ?? 'Generica',
                        'line' => $row['Linea'] ?? $row['linea'] ?? 'Estandar',
                        'model_year' => (int) ($row['Modelo'] ?? $row['modelo'] ?? 2020),
                        'color' => $row['Color'] ?? $row['color'] ?? 'Blanco',
                        'vehicle_type' => $row['TipoVehiculo'] ?? $row['ClaseVehiculo'] ?? 'Microbus',
                        'passenger_capacity' => (int) ($row['Capacidad'] ?? $row['capacidad'] ?? 19),
                        'current_mileage' => (float) ($row['KilometrajeActual'] ?? $row['kilometraje_actual'] ?? 0),
                        'partner_id' => $partnerId,
                        'soat_expiration' => $this->parseDate($row['FechaVencimientoSoat'] ?? $row['soat_expiration'] ?? null),
                        'technomechanical_expiration' => $this->parseDate($row['FechaVencimientoTecno'] ?? $row['technomechanical_expiration'] ?? null),
                        'contractual_policy_expiration' => $this->parseDate($row['FechaVencimientoPoliza'] ?? $row['contractual_policy_expiration'] ?? null),
                        'extra_contractual_policy_expiration' => $this->parseDate($row['FechaVencimientoPolizaExtra'] ?? $row['extra_contractual_policy_expiration'] ?? null),
                        'operation_card_expiration' => $this->parseDate($row['FechaVencimientoTarjetaOperacion'] ?? $row['operation_card_expiration'] ?? null),
                        'status' => 'Activo',
                    ]
                );

                if ($oldId) {
                    $this->vehicleMap[$oldId] = $vehicle->id;
                }
            }

            $this->counts['vehicles']['imported']++;
        }
    }

    protected function migrateEmployees(array $rows, bool $dryRun): void
    {
        $this->counts['employees']['extracted'] = count($rows);

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? $row['ID'] ?? null;
            $doc = trim($row['NumeroDocumento'] ?? $row['cedula'] ?? $row['documento'] ?? '');
            $name = trim(($row['Nombres'] ?? $row['nombre'] ?? '').' '.($row['Apellidos'] ?? ''));

            if (empty($doc) || empty($name)) {
                $this->counts['employees']['skipped']++;
                $this->skipped[] = "Empleado ID #{$oldId}: omitido por falta de documento o nombre.";

                continue;
            }

            $cargo = strtolower($row['Cargo'] ?? $row['rol'] ?? 'conductor');
            $employeeType = (str_contains($cargo, 'conductor') || str_contains($cargo, 'driver')) ? 'Conductor' : 'Administrativo';

            if (! $dryRun) {
                // Crear usuario vinculado si tiene email
                $email = $row['Correo'] ?? $row['email'] ?? "empleado{$oldId}@sistransporte.local";
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'username' => 'user_'.$doc,
                        'password' => Hash::make('cambiame123'),
                        'is_active' => true,
                    ]
                );

                $employee = Employee::updateOrCreate(
                    ['document_number' => $doc],
                    [
                        'user_id' => $user->id,
                        'name' => $name,
                        'phone' => $row['Telefono'] ?? $row['celular'] ?? null,
                        'email' => $email,
                        'employee_type' => $employeeType,
                        'contract_number' => $row['NumeroContrato'] ?? 'CTR-LEG-'.$oldId,
                        'contract_type' => 'Termino Fijo',
                        'contract_start_date' => $this->parseDate($row['FechaInicioContrato'] ?? null),
                        'contract_end_date' => $this->parseDate($row['FechaFinContrato'] ?? null),
                        'driver_license_number' => $row['NumeroLicencia'] ?? $row['licencia'] ?? null,
                        'driver_license_category' => $row['CategoriaLicencia'] ?? 'C2',
                        'driver_license_expiration' => $this->parseDate($row['FechaVencimientoLicencia'] ?? null),
                        'partner_id' => $this->partnerMap[$row['proveedor_id'] ?? $row['aliadoid'] ?? null] ?? null,
                        'status' => 'Activo',
                    ]
                );

                if ($oldId) {
                    $this->employeeMap[$oldId] = $employee->id;
                }
            }

            $this->counts['employees']['imported']++;
        }
    }

    protected function migrateContracts(array $rows, bool $dryRun): void
    {
        $this->counts['contracts']['extracted'] = count($rows);

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? $row['ID'] ?? null;
            $contractNumber = trim($row['NumeroContrato'] ?? $row['numero_contrato'] ?? 'CTR-LEG-'.$oldId);
            $oldClientId = $row['cliente_id'] ?? $row['clienteid'] ?? null;
            $clientId = $oldClientId ? ($this->clientMap[$oldClientId] ?? null) : null;

            if (! $clientId) {
                // Asignar al primer cliente disponible o crear uno general
                $firstClient = Client::first();
                $clientId = $firstClient ? $firstClient->id : null;
            }

            if (! $clientId) {
                $this->counts['contracts']['skipped']++;
                $this->skipped[] = "Contrato #{$contractNumber}: omitido por no encontrar cliente vinculado.";

                continue;
            }

            $rawType = $row['TipoContrato'] ?? $row['tipo_contrato'] ?? $row['contract_type'] ?? 'Empresarial';
            $validTypes = ['Empresarial', 'Turismo', 'Escolar', 'Salud', 'Grupo Especifico'];
            $contractType = in_array($rawType, $validTypes) ? $rawType : 'Empresarial';

            $rawStatus = $row['Estado'] ?? $row['estado'] ?? 'Vigente';
            $status = in_array($rawStatus, ['Vigente', 'Vencido', 'Cancelado']) ? $rawStatus : 'Vigente';

            if (! $dryRun) {
                Contract::firstOrCreate(
                    ['contract_number' => $contractNumber],
                    [
                        'client_id' => $clientId,
                        'contract_type' => $contractType,
                        'contract_object' => $row['Objeto'] ?? $row['objeto'] ?? 'Servicio de transporte empresarial y escolar',
                        'start_date' => $this->parseDate($row['FechaInicio'] ?? $row['fecha_inicio'] ?? null) ?? now()->toDateString(),
                        'end_date' => $this->parseDate($row['FechaFin'] ?? $row['fecha_fin'] ?? null) ?? now()->addYear()->toDateString(),
                        'value' => (float) ($row['Valor'] ?? $row['valor'] ?? 0),
                        'status' => $status,
                    ]
                );
            }

            $this->counts['contracts']['imported']++;
        }
    }

    protected function parseDate(?string $value): ?string
    {
        if (! $value || $value === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function fetchLegacyTable(?string $connectionName, string $table): ?array
    {
        try {
            return DB::connection($connectionName)->table($table)->get()->map(fn ($r) => (array) $r)->toArray();
        } catch (\Throwable $e) {
            Log::info("Tabla legada {$table} no encontrada o sin conexión ({$e->getMessage()}).");

            return null;
        }
    }

    protected function notify(?callable $onProgress, string $message): void
    {
        if ($onProgress) {
            $onProgress($message);
        }
    }
}
