<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\Partner;
use App\Models\Tenant\Route as TenantRoute;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Services\Etl\SqlDumpExtractor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyTransporteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:import-legacy
                            {--file=transporteprivado.sql : Ruta al archivo dump .sql}
                            {--tenant=empresa1 : ID del Tenant de destino}
                            {--dry-run : Ejecutar en modo simulación sin persistir cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa y migra los datos de la versión 1 (dump SQL transporteprivado) a la arquitectura multi-tenant optimizada.';

    public function handle(SqlDumpExtractor $extractor): int
    {
        $fileName = $this->option('file');
        $filePath = base_path($fileName);
        $tenantId = $this->option('tenant');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('========================================================================');
        $this->info(' ETL MIGRACIÓN MAESTRA DE DATOS V1 -> V2 (SIS TRANSPORTE)');
        $this->info('========================================================================');
        $this->line("Archivo Origen : <comment>{$filePath}</comment>");
        $this->line("Tenant Destino : <comment>{$tenantId}</comment>");
        $this->line('Modo Operación : '.($dryRun ? '<fg=yellow>SIMULACIÓN (DRY-RUN)</>' : '<fg=green>PERSISTENCIA REAL</>'));
        $this->newLine();

        if (! file_exists($filePath)) {
            $this->error("Error: El archivo '{$filePath}' no existe.");

            return Command::FAILURE;
        }

        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            $this->error("Error: El tenant '{$tenantId}' no fue encontrado.");

            return Command::FAILURE;
        }

        $startTime = microtime(true);

        $tablesToExtract = [
            'aliados',
            'clientes',
            'empleados',
            'vehiculos',
            'contratos_administracion',
            'contratos_aliados',
            'rutas',
            'combustible',
            'mantenimientos',
            'ordenesservicio',
        ];

        $counts = [
            'partners' => 0,
            'clients' => 0,
            'employees' => 0,
            'vehicles' => 0,
            'contracts' => 0,
            'routes' => 0,
            'service_orders' => 0,
            'fuel_refills' => 0,
            'maintenances' => 0,
        ];

        $tenant->run(function () use ($extractor, $filePath, $tablesToExtract, $dryRun, &$counts) {
            $this->line('<fg=cyan>➜</> Conectado a la base de datos del tenant. Extrayendo datos en streaming...');

            if (! $dryRun) {
                DB::statement('PRAGMA foreign_keys = OFF;');
                DB::beginTransaction();
            }

            try {
                // Mapas de claves primarias legadas a nuevas
                $partnerIds = [];
                $clientIds = [];
                $employeeIds = [];
                $vehicleIds = [];
                $contractIds = [];
                $routeMap = [];

                // Pre-cargar IDs existentes del tenant para no duplicar ni romper FKs
                $partnerIds = Partner::pluck('id', 'id')->toArray();
                $clientIds = Client::pluck('id', 'id')->toArray();
                $employeeIds = Employee::pluck('id', 'id')->toArray();
                $vehicleIds = Vehicle::pluck('id', 'id')->toArray();
                $contractIds = Contract::pluck('id', 'id')->toArray();

                foreach ($extractor->streamTableRows($filePath, $tablesToExtract) as $table => $row) {
                    switch ($table) {
                        case 'aliados':
                            $id = (int) ($row['id'] ?? 0);
                            $name = trim($row['nombre'] ?? '');
                            if ($name === '') {
                                continue 2;
                            }

                            if (! $dryRun) {
                                $partner = Partner::updateOrCreate(
                                    ['name' => $name],
                                    [
                                        'nit' => trim($row['nit'] ?? '') ?: null,
                                        'phone' => trim($row['telefono'] ?? '') ?: null,
                                        'email' => trim($row['email'] ?? '') ?: null,
                                        'address' => trim($row['direccion'] ?? '') ?: null,
                                        'status' => ($row['estado'] ?? 'Activo') === 'Activo' ? 'Activo' : 'Inactivo',
                                    ]
                                );
                                $partnerIds[$id] = $partner->id;
                            } else {
                                $partnerIds[$id] = $id;
                            }
                            $counts['partners']++;
                            break;

                        case 'clientes':
                            $id = (int) ($row['ID'] ?? 0);
                            $tipo = ($row['Tipo'] ?? '') === 'Empresa' ? 'Empresa' : 'Persona Natural';
                            $doc = trim($row['Identificacion'] ?? '');
                            if ($doc === '') {
                                $doc = 'SIN-DOC-'.$id;
                            }

                            $businessName = trim($row['RazonSocial'] ?? $row['NombreComercial'] ?? '');
                            $firstName = trim($row['Nombres'] ?? '');
                            $lastName = trim($row['Apellidos'] ?? '');

                            if ($tipo === 'Empresa' && $businessName === '') {
                                $businessName = trim("{$firstName} {$lastName}") ?: "Empresa {$doc}";
                            }

                            if (! $dryRun) {
                                $client = Client::updateOrCreate(
                                    ['document_number' => $doc],
                                    [
                                        'type' => $tipo,
                                        'business_name' => $businessName ?: null,
                                        'first_name' => $firstName ?: null,
                                        'last_name' => $lastName ?: null,
                                        'phone' => trim($row['Telefono'] ?? '') ?: null,
                                        'address' => trim($row['Direccion'] ?? '') ?: null,
                                        'status' => ($row['estado'] ?? 'Activo') === 'Activo' ? 'Activo' : 'Inactivo',
                                    ]
                                );
                                $clientIds[$id] = $client->id;
                            } else {
                                $clientIds[$id] = $id;
                            }
                            $counts['clients']++;
                            break;

                        case 'empleados':
                            $id = (int) ($row['ID'] ?? 0);
                            $name = trim($row['Nombre'] ?? '');
                            $doc = trim($row['CC'] ?? '');
                            if ($name === '') {
                                continue 2;
                            }
                            if ($doc === '') {
                                $doc = 'EMP-'.$id;
                            }

                            $partnerId = isset($row['aliadoid']) && isset($partnerIds[(int) $row['aliadoid']])
                                ? (int) $row['aliadoid']
                                : null;

                            $email = trim($row['Correo'] ?? '');
                            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $email = "empleado_{$id}@transporteskemuel.test";
                            }

                            if (! $dryRun) {
                                $emp = Employee::updateOrCreate(
                                    ['document_number' => $doc],
                                    [
                                        'partner_id' => $partnerId,
                                        'name' => $name,
                                        'phone' => trim($row['NumeroTelefono'] ?? '') ?: null,
                                        'email' => $email,
                                        'address' => trim($row['Direccion'] ?? '') ?: null,
                                        'employee_type' => 'Conductor',
                                        'contract_number' => trim($row['NumeroContrato'] ?? '') ?: null,
                                        'contract_start_date' => $this->cleanDate($row['FechaInicio'] ?? null),
                                        'contract_end_date' => $this->cleanDate($row['FechaFin'] ?? null),
                                        'status' => ($row['Estado'] ?? 'Activo') === 'Activo' ? 'Activo' : 'Inactivo',
                                    ]
                                );
                                $employeeIds[$id] = $emp->id;
                            } else {
                                $employeeIds[$id] = $id;
                            }
                            $counts['employees']++;
                            break;

                        case 'vehiculos':
                            $id = (int) ($row['ID'] ?? 0);
                            $plate = strtoupper(trim($row['Placa'] ?? ''));
                            if ($plate === '') {
                                continue 2;
                            }

                            $partnerId = isset($row['aliadoid']) && isset($partnerIds[(int) $row['aliadoid']])
                                ? (int) $row['aliadoid']
                                : null;

                            $mileage = (float) ($row['KilometrajeActual'] ?? 0);
                            if ($mileage > 2000000) { // Sanear odómetros descalibrados
                                $mileage = 150000;
                            }

                            $year = (int) ($row['Modelo'] ?? 2020);
                            if ($year < 1980 || $year > 2030) {
                                $year = 2020;
                            }

                            if (! $dryRun) {
                                $veh = Vehicle::updateOrCreate(
                                    ['plate' => $plate],
                                    [
                                        'plate' => $plate,
                                        'internal_number' => trim($row['NumeroInterno'] ?? '') ?: null,
                                        'brand' => trim($row['Marca'] ?? '') ?: 'RENAULT',
                                        'line' => trim($row['ClaseVehiculo'] ?? '') ?: 'DUSTER',
                                        'model_year' => $year,
                                        'color' => trim($row['Color'] ?? '') ?: 'BLANCO',
                                        'engine_number' => trim($row['NumeroMotor'] ?? '') ?: 'S/N',
                                        'chassis_number' => trim($row['NumeroChasis'] ?? '') ?: 'S/N',
                                        'vehicle_type' => $this->mapVehicleType($row['TipoVehiculo'] ?? ''),
                                        'passenger_capacity' => 4,
                                        'current_mileage' => $mileage,
                                        'partner_id' => $partnerId,
                                        'status' => ($row['Estado'] ?? 'Activo') === 'Activo' ? 'Activo' : 'Inactivo',
                                    ]
                                );
                                $vehicleIds[$id] = $veh->id;
                            } else {
                                $vehicleIds[$id] = $id;
                            }
                            $counts['vehicles']++;
                            break;

                        case 'contratos_administracion':
                        case 'contratos_aliados':
                            $id = (int) ($row['id'] ?? 0);
                            $clientId = (int) ($row['cliente_id'] ?? 0);

                            if (! isset($clientIds[$clientId])) {
                                continue 2;
                            }

                            $prefix = $table === 'contratos_administracion' ? 'CT-ADM-' : 'CT-ALI-';
                            $contractNum = $prefix.str_pad((string) $id, 4, '0', STR_PAD_LEFT);

                            $startDate = $this->cleanDate($row['fecha_inicio'] ?? null) ?? now()->toDateString();
                            $endDate = $this->cleanDate($row['fecha_fin'] ?? null) ?? now()->addYear()->toDateString();

                            $status = Carbon::parse($endDate)->isPast() ? 'Vencido' : 'Vigente';

                            if (! $dryRun) {
                                $contrato = Contract::updateOrCreate(
                                    ['contract_number' => $contractNum],
                                    [
                                        'client_id' => $clientIds[$clientId],
                                        'contract_object' => trim($row['objeto_contrato'] ?? 'Prestación de servicio de transporte especial'),
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'value' => 0,
                                        'contract_type' => 'Empresarial',
                                        'status' => $status,
                                    ]
                                );
                                $contractIds[$id] = $contrato->id;
                            } else {
                                $contractIds[$id] = $id;
                            }
                            $counts['contracts']++;
                            break;

                        case 'rutas':
                            $id = (int) ($row['ID'] ?? 0);
                            $origen = trim($row['PuntoInicio'] ?? '');
                            $destino = trim($row['PuntoFin'] ?? '');
                            if ($origen === '' && $destino === '') {
                                continue 2;
                            }

                            $name = "{$origen} - {$destino}";
                            $routeMap[$id] = [
                                'origin' => $origen ?: 'Cartagena',
                                'destination' => $destino ?: 'Cartagena',
                            ];

                            if (! $dryRun) {
                                TenantRoute::updateOrCreate(
                                    ['id' => $id],
                                    [
                                        'name' => substr($name, 0, 150),
                                        'origin' => substr($origen ?: 'Cartagena', 0, 150),
                                        'destination' => substr($destino ?: 'Cartagena', 0, 150),
                                        'description' => trim($row['Descripcion'] ?? ''),
                                        'is_active' => ($row['Estado'] ?? 'Activa') === 'Activa',
                                    ]
                                );
                            }
                            $counts['routes']++;
                            break;

                        case 'ordenesservicio':
                            $id = (int) ($row['ID'] ?? 0);
                            $clientId = (int) ($row['ClienteID'] ?? 0);

                            if (! isset($clientIds[$clientId])) {
                                continue 2;
                            }

                            $orderNum = 'ODS-V1-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
                            $contractId = isset($row['contrato_id']) && isset($contractIds[(int) $row['contrato_id']])
                                ? $contractIds[(int) $row['contrato_id']]
                                : null;

                            $vehId = isset($row['VehiculoID']) && isset($vehicleIds[(int) $row['VehiculoID']])
                                ? $vehicleIds[(int) $row['VehiculoID']]
                                : null;

                            $empId = isset($row['EmpleadoID']) && isset($employeeIds[(int) $row['EmpleadoID']])
                                ? $employeeIds[(int) $row['EmpleadoID']]
                                : null;

                            $rutaId = (int) ($row['RutaID'] ?? 0);
                            $origin = $routeMap[$rutaId]['origin'] ?? 'Cartagena D.T. y C.';
                            $destination = $routeMap[$rutaId]['destination'] ?? 'Cartagena / Zona Industrial';

                            $start = $this->cleanDateTime($row['FechaInicio'] ?? $row['inicio'] ?? $row['FechaCreacion'] ?? null) ?? now();
                            $end = $this->cleanDateTime($row['fin'] ?? null) ?? Carbon::parse($start)->addHours(4);

                            if (! $dryRun) {
                                ServiceOrder::updateOrCreate(
                                    ['order_number' => $orderNum],
                                    [
                                        'client_id' => $clientIds[$clientId],
                                        'contract_id' => $contractId,
                                        'vehicle_id' => $vehId,
                                        'driver_id' => $empId,
                                        'origin' => substr($origin, 0, 250),
                                        'destination' => substr($destination, 0, 250),
                                        'scheduled_start_time' => $start,
                                        'scheduled_end_time' => $end,
                                        'passengers_count' => (int) ($row['cantidad_pasajeros'] ?? 1) ?: 1,
                                        'fare' => (float) ($row['Tarifa'] ?? $row['tarifa'] ?? 0),
                                        'start_mileage' => (float) ($row['KilometrajeInicial'] ?? 0) ?: null,
                                        'end_mileage' => (float) ($row['KilometrajeFinal'] ?? 0) ?: null,
                                        'invoice_number' => trim($row['numero_factura'] ?? '') ?: null,
                                        'is_paid' => (int) ($row['pago'] ?? 0) === 1,
                                        'paid_at' => ((int) ($row['pago'] ?? 0) === 1) ? ($this->cleanDateTime($row['fin'] ?? null) ?? now()) : null,
                                        'service_notes' => trim($row['Descripcion'] ?? '') ?: null,
                                        'status' => $this->mapOrderStatus($row['Estado'] ?? ''),
                                    ]
                                );
                            }
                            $counts['service_orders']++;
                            break;

                        case 'combustible':
                            $id = (int) ($row['ID'] ?? 0);
                            $vehId = (int) ($row['vehiculo_id'] ?? 0);

                            if (! isset($vehicleIds[$vehId])) {
                                continue 2;
                            }

                            $empId = isset($row['empleado_id']) && isset($employeeIds[(int) $row['empleado_id']])
                                ? $employeeIds[(int) $row['empleado_id']]
                                : null;

                            $gallons = (float) ($row['CantidadCombustible'] ?? 0);
                            $cost = (float) ($row['Costo'] ?? 0);
                            $mileage = (float) ($row['Kilometraje'] ?? 0);
                            if ($mileage > 2000000) {
                                $mileage = 150000;
                            }

                            $date = $this->cleanDate($row['Fecha'] ?? null) ?? now()->toDateString();

                            if (! $dryRun) {
                                FuelRefill::updateOrCreate(
                                    ['id' => $id],
                                    [
                                        'vehicle_id' => $vehicleIds[$vehId],
                                        'driver_id' => $empId,
                                        'refill_date' => $date,
                                        'gallons' => $gallons > 0 ? $gallons : 1.0,
                                        'total_cost' => $cost >= 0 ? $cost : 0,
                                        'price_per_gallon' => $gallons > 0 ? ($cost / $gallons) : 0,
                                        'odometer_mileage' => $mileage,
                                        'gas_station_name' => 'Estación de Servicio Central',
                                        'receipt_photo' => trim($row['foto_comprobante'] ?? '') ?: null,
                                        'odometer_photo' => trim($row['foto_kilometraje'] ?? '') ?: null,
                                    ]
                                );
                            }
                            $counts['fuel_refills']++;
                            break;

                        case 'mantenimientos':
                            $id = (int) ($row['ID'] ?? 0);
                            $vehId = (int) ($row['VehiculoID'] ?? 0);

                            if (! isset($vehicleIds[$vehId])) {
                                continue 2;
                            }

                            $mileage = (float) ($row['Kilometraje'] ?? 0);
                            if ($mileage > 2000000) {
                                $mileage = 150000;
                            }

                            $date = $this->cleanDate($row['FechaMantenimiento'] ?? null) ?? now()->toDateString();
                            $cost = (float) ($row['ValorMantenimiento'] ?? 0);

                            if (! $dryRun) {
                                Maintenance::updateOrCreate(
                                    ['id' => $id],
                                    [
                                        'vehicle_id' => $vehicleIds[$vehId],
                                        'maintenance_type' => ($row['TipoMantenimiento'] ?? '') === 'Preventivo' ? 'Preventivo' : 'Correctivo',
                                        'maintenance_date' => $date,
                                        'mileage' => $mileage,
                                        'cost' => $cost >= 0 ? $cost : 0,
                                        'workshop_name' => 'Taller Autorizado Kemuel',
                                        'details' => trim($row['DetalleMantenimiento'] ?? '') ?: 'Mantenimiento preventivo / correctivo registrado en V1',
                                        'receipt_file' => trim($row['archivo'] ?? '') ?: null,
                                    ]
                                );
                            }
                            $counts['maintenances']++;
                            break;
                    }
                }

                if (! $dryRun) {
                    DB::commit();
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
            } catch (\Throwable $e) {
                if (! $dryRun) {
                    DB::rollBack();
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
                throw $e;
            }
        });

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('========================================================================');
        $this->info(' RESUMEN CONSOLIDADO DE LA MIGRACIÓN');
        $this->info('========================================================================');

        $tableRows = [
            ['Aliados / Proveedores', $counts['partners']],
            ['Clientes Corporativos / Particulares', $counts['clients']],
            ['Personal / Conductores', $counts['employees']],
            ['Flota Vehicular', $counts['vehicles']],
            ['Contratos de Transporte', $counts['contracts']],
            ['Catálogo de Rutas', $counts['routes']],
            ['Órdenes de Servicio', $counts['service_orders']],
            ['Registros de Combustible', $counts['fuel_refills']],
            ['Bitácora de Mantenimientos', $counts['maintenances']],
        ];

        $this->table(['Módulo / Entidad', 'Registros Procesados'], $tableRows);
        $this->info("Tiempo Total de Ejecución: {$duration} segundos.");
        $this->info('¡Migración ETL completada exitosamente!');

        return Command::SUCCESS;
    }

    protected function cleanDate(?string $date): ?string
    {
        if (! $date || str_starts_with($date, '0000')) {
            return null;
        }
        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function cleanDateTime(?string $dateTime): ?string
    {
        if (! $dateTime || str_starts_with($dateTime, '0000')) {
            return null;
        }
        try {
            return Carbon::parse($dateTime)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function mapVehicleType(string $legacyType): string
    {
        return match ($legacyType) {
            'Automovil' => 'Automovil',
            'Camioneta 4x4', 'Camioneta platon' => 'Camioneta',
            'Bus' => 'Bus',
            'Buseta' => 'Buseta',
            'MicroBus' => 'Microbus',
            default => 'Van',
        };
    }

    protected function mapOrderStatus(string $legacyStatus): string
    {
        return match ($legacyStatus) {
            'En Progreso' => 'En Progreso',
            'Completado' => 'Finalizada',
            'Cancelado' => 'Cancelada',
            default => 'Pendiente',
        };
    }
}
