<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Etl\LegacyDataMigrationService;
use Illuminate\Console\Command;

class MigrateLegacyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy-data
                            {--tenant=empresa1 : ID del Tenant de destino}
                            {--connection=legacy : Conexión de base de datos legada}
                            {--dry-run : Ejecutar en modo simulación sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extrae, transforma y carga (ETL) los datos del sistema legado transporteprivado al nuevo modelo multi-tenant.';

    public function handle(LegacyDataMigrationService $migrationService): int
    {
        $tenantId = $this->option('tenant');
        $connection = $this->option('connection');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('================================================================');
        $this->info(' ETL MIGRACIÓN DE DATOS LEGADOS: SIS TRANSPORTE V1 -> V2');
        $this->info('================================================================');
        $this->line("Tenant Destino : <comment>{$tenantId}</comment>");
        $this->line("Conexión BD    : <comment>{$connection}</comment>");
        $this->line('Modo           : '.($dryRun ? '<fg=yellow>SIMULACIÓN (DRY-RUN)</>' : '<fg=green>EJECUCIÓN REAL</>'));
        $this->newLine();

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Error: El tenant '{$tenantId}' no existe en la base de datos central.");

            return Command::FAILURE;
        }

        $result = $migrationService->migrate(
            tenant: $tenant,
            dryRun: $dryRun,
            connectionName: $connection,
            customData: [],
            onProgress: fn ($msg) => $this->line(" <fg=cyan>➜</> {$msg}")
        );

        $this->newLine();
        $this->info('----------------------------------------------------------------');
        $this->info(' RESUMEN DEL PROCESO DE MIGRACIÓN');
        $this->info('----------------------------------------------------------------');

        $rows = [];
        foreach ($result['counts'] as $entity => $stats) {
            $rows[] = [
                ucfirst($entity),
                $stats['extracted'],
                $stats['imported'],
                $stats['skipped'],
            ];
        }

        $this->table(['Entidad', 'Extraídos', 'Importados', 'Omitidos'], $rows);

        if (! empty($result['skipped'])) {
            $this->warn('Registros omitidos durante la migración:');
            foreach ($result['skipped'] as $skipped) {
                $this->line("  <fg=yellow>⚠</> {$skipped}");
            }
        }

        $this->newLine();
        $this->info("Tiempo de ejecución: {$result['duration_seconds']}s");
        $this->info('Migración finalizada con éxito.');

        return Command::SUCCESS;
    }
}
