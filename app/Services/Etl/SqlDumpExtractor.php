<?php

namespace App\Services\Etl;

use Generator;

class SqlDumpExtractor
{
    /**
     * Definición de columnas por defecto para tablas sin lista de campos en el INSERT.
     */
    protected static array $defaultColumns = [
        'aliados' => ['id', 'nombre', 'nit', 'telefono', 'direccion', 'email', 'estado', 'proveedor_id'],
        'clientes' => ['ID', 'Tipo', 'TipoIdentificacion', 'Identificacion', 'DV', 'CodigoSucursal', 'Nombres', 'Apellidos', 'RazonSocial', 'NombreComercial', 'Ciudad', 'Direccion', 'Telefono', 'Extension', 'estado', 'aliadoid'],
        'empleados' => ['ID', 'Nombre', 'CC', 'NumeroContrato', 'FechaInicio', 'FechaFin', 'Sueldo', 'TipoContrato', 'NumeroTelefono', 'Correo', 'Direccion', 'Foto', 'Estado', 'TipoTrabajador', 'rol', 'proveedor_id', 'aliadoid'],
        'vehiculos' => ['ID', 'Placa', 'cilindraje', 'combustible', 'KilometrajeActual', 'DiferenciaKilometraje', 'FechaMatricula', 'LugarMatricula', 'Modelo', 'Marca', 'ClaseVehiculo', 'TipoVehiculo', 'NumeroMotor', 'NumeroChasis', 'Color', 'LegitimoPropietario', 'Estado', 'proveedor_id', 'NumeroInterno', 'aliadoid'],
        'contratos_administracion' => ['id', 'cliente_id', 'objeto_contrato', 'fecha_inicio', 'fecha_fin', 'created_at', 'updated_at', 'nit', 'razon_social', 'correo_empresa', 'telefono_empresa'],
        'contratos_aliados' => ['id', 'cliente_id', 'objeto_contrato', 'fecha_inicio', 'fecha_fin', 'created_at', 'updated_at', 'nit', 'razon_social', 'correo_empresa', 'telefono_empresa'],
        'combustible' => ['ID', 'Fecha', 'CantidadCombustible', 'Costo', 'Kilometraje', 'vehiculo_id', 'empleado_id', 'orden_servicio_id', 'foto_kilometraje', 'foto_comprobante'],
        'mantenimientos' => ['ID', 'TipoMantenimiento', 'FechaMantenimiento', 'VehiculoID', 'ValorMantenimiento', 'Kilometraje', 'archivo', 'DetalleMantenimiento', 'ParteID', 'mantenimiento_procesado', 'aliadoid'],
        'ordenesservicio' => ['ID', 'ClienteID', 'contrato_id', 'VehiculoID', 'RutaID', 'EmpleadoID', 'FechaCreacion', 'Descripcion', 'Estado', 'ApoyoSolicitado', 'evento', 'centro_costos', 'Tarifa', 'tarifa_aliado', 'ganancia', 'cantidad_pasajeros', 'FechaInicio', 'inicio', 'fin', 'KilometrajeInicial', 'KilometrajeFinal'],
        'rutas' => ['ID', 'Descripcion', 'TipoRuta', 'FechaCreacion', 'Estado', 'PuntoInicio', 'PuntoFin'],
    ];

    /**
     * Lee un archivo .sql de forma progresiva (streaming) y extrae registros de las tablas objetivo.
     *
     * @param  string  $filePath  Ruta absoluta al archivo SQL
     * @param  array  $targetTables  Lista de tablas a extraer
     * @return Generator<string, array> Genera pares [nombre_tabla, fila_asociativa]
     */
    public function streamTableRows(string $filePath, array $targetTables): Generator
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new \RuntimeException("Archivo no encontrado o sin permisos de lectura: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException("No se pudo abrir el archivo SQL: {$filePath}");
        }

        $targetSet = array_flip($targetTables);
        $buffer = '';

        while (($line = fgets($handle, 4194304)) !== false) { // 4MB buffer per line chunk
            // Si la línea no contiene INSERT INTO, la descartamos de inmediato
            if (! str_starts_with(ltrim($line), 'INSERT INTO')) {
                continue;
            }

            // Detectar tabla
            if (! preg_match('/^INSERT INTO `?([a-zA-Z0-9_]+)`?(?: \(([^)]+)\))? VALUES\s*(.+)$/i', trim($line), $matches)) {
                continue;
            }

            $table = $matches[1];
            if (! isset($targetSet[$table])) {
                continue; // Saltar tablas que no nos interesan
            }

            // Obtener nombres de columnas
            if (! empty($matches[2])) {
                $columns = array_map(fn ($col) => trim($col, " `'\t\n\r\0\x0B"), explode(',', $matches[2]));
            } else {
                $columns = self::$defaultColumns[$table] ?? [];
            }

            $valuesStr = $matches[3];
            $rows = self::parseTuples($valuesStr);

            foreach ($rows as $rowValues) {
                if (empty($columns)) {
                    yield $table => $rowValues;
                } else {
                    $assoc = [];
                    foreach ($columns as $idx => $colName) {
                        $assoc[$colName] = $rowValues[$idx] ?? null;
                    }
                    yield $table => $assoc;
                }
            }
        }

        fclose($handle);
    }

    /**
     * Parser rápido por estados para tuplas SQL: (val1, 'val2', NULL), (...)
     */
    public static function parseTuples(string $valuesSql): array
    {
        $len = strlen($valuesSql);
        $rows = [];
        $currentRow = [];
        $currentVal = '';
        $inString = false;
        $inTuple = false;
        $escape = false;

        for ($i = 0; $i < $len; $i++) {
            $c = $valuesSql[$i];

            if ($escape) {
                $currentVal .= $c;
                $escape = false;

                continue;
            }

            if ($c === '\\') {
                $escape = true;

                continue;
            }

            if ($c === "'") {
                $inString = ! $inString;

                continue;
            }

            if (! $inString) {
                if ($c === '(' && ! $inTuple) {
                    $inTuple = true;
                    $currentRow = [];
                    $currentVal = '';

                    continue;
                } elseif ($c === ')' && $inTuple) {
                    $inTuple = false;
                    $trimmed = trim($currentVal);
                    $currentRow[] = ($trimmed === 'NULL') ? null : $trimmed;
                    $rows[] = $currentRow;
                    $currentRow = [];
                    $currentVal = '';

                    continue;
                } elseif ($c === ',' && $inTuple) {
                    $trimmed = trim($currentVal);
                    $currentRow[] = ($trimmed === 'NULL') ? null : $trimmed;
                    $currentVal = '';

                    continue;
                } elseif ($c === ';' && ! $inTuple) {
                    break;
                }
            }

            if ($inTuple) {
                $currentVal .= $c;
            }
        }

        return $rows;
    }
}
