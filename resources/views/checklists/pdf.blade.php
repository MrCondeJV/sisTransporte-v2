<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inspección Preoperacional - {{ $checklist->vehicle?->plate ?? 'Vehiculo' }}</title>
    <style>
        @page {
            margin: 1cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 7.5pt;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }
        .header-table {
            border: 2px solid #0f172a;
            margin-bottom: 12px;
        }
        .header-table td {
            border: 1px solid #0f172a;
        }
        .title {
            font-size: 9.5pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }
        .subtitle {
            font-size: 7.5pt;
            text-align: center;
            color: #475569;
            margin-top: 2px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .section-header {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
            padding: 4px 6px;
            text-transform: uppercase;
        }
        .signatures-table td {
            height: 65px;
            vertical-align: bottom;
            text-align: center;
            padding-bottom: 6px;
        }
    </style>
</head>
<body>

    <!-- Header institucional -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center; font-weight: bold; font-size: 11pt; color: #0284c7;">
                SISTRANSPORTE
                <div style="font-size: 6.5pt; color: #64748b; font-weight: normal;">SISTEMA OPERATIVO Y VIAL</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="title">FORMATO DE INSPECCIÓN PREOPERACIONAL DE VEHÍCULOS</div>
                <div class="subtitle">Plan Estratégico de Seguridad Vial (PESV) - Res. 20223040040595</div>
            </td>
            <td style="width: 25%; font-size: 7pt;">
                <b>Código:</b> FT-SST-004<br>
                <b>Versión:</b> 03<br>
                <b>Acta N°:</b> #{{ str_pad($checklist->id, 5, '0', STR_PAD_LEFT) }}<br>
                <b>Fecha Exp:</b> {{ now()->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <!-- Datos del Vehículo y Conductor -->
    <table>
        <tr>
            <th style="width: 15%;">Fecha & Hora:</th>
            <td style="width: 35%;">{{ $checklist->date?->format('d/m/Y') }} — {{ $checklist->time }}</td>
            <th style="width: 15%;">Placa Vehículo:</th>
            <td style="width: 35%; font-weight: bold; font-size: 8.5pt;">{{ $checklist->vehicle?->plate ?? 'N/A' }} ({{ $checklist->vehicle?->brand }} {{ $checklist->vehicle?->line }})</td>
        </tr>
        <tr>
            <th>Conductor:</th>
            <td>{{ $checklist->driver?->name ?? 'No registrado' }}</td>
            <th>C.C. Conductor:</th>
            <td>{{ $checklist->driver?->identification_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Licencia Conducción:</th>
            <td>{{ $checklist->driver?->driver_license_number ?? 'N/A' }}</td>
            <th>Kilometraje (Odómetro):</th>
            <td style="font-weight: bold;">{{ number_format($checklist->mileage, 0) }} Km</td>
        </tr>
        <tr>
            <th>Orden de Servicio:</th>
            <td>{{ $checklist->serviceOrder ? 'ODS #' . $checklist->serviceOrder->order_number : 'Turno General' }}</td>
            <th>Resultado de Auditoría:</th>
            <td>
                @if($checklist->is_approved)
                    <span class="badge badge-success">APROBADO — APTO PARA OPERAR</span>
                @else
                    <span class="badge badge-danger">RECHAZADO — NO APTO PARA OPERAR</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Tabla de Ítems Auditados -->
    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Ítem Evaluado</th>
                <th style="width: 15%; text-align: center;">Estado</th>
                <th style="width: 35%;">Ítem Evaluado</th>
                <th style="width: 15%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fluidos y Luces -->
            <tr>
                <td colspan="2" class="section-header">1. Niveles de Fluidos</td>
                <td colspan="2" class="section-header">2. Luces y Sistema Eléctrico</td>
            </tr>
            @php
                $fluidos = $checklist->fluid_levels ?? [];
                $luces = $checklist->lights_and_electrical ?? [];
                $kFlu = array_keys($fluidos);
                $kLuc = array_keys($luces);
                $max1 = max(count($kFlu), count($kLuc), 1);
            @endphp
            @for($i = 0; $i < $max1; $i++)
                <tr>
                    <td>{{ isset($kFlu[$i]) ? ucwords(str_replace('_', ' ', $kFlu[$i])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kFlu[$i]))
                            @php $val1 = (string)($fluidos[$kFlu[$i]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val1), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val1 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                    <td>{{ isset($kLuc[$i]) ? ucwords(str_replace('_', ' ', $kLuc[$i])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kLuc[$i]))
                            @php $val2 = (string)($luces[$kLuc[$i]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val2), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val2 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endfor

            <!-- Neumáticos y Equipo -->
            <tr>
                <td colspan="2" class="section-header">3. Neumáticos y Frenos</td>
                <td colspan="2" class="section-header">4. Equipo de Prevención y Seguridad</td>
            </tr>
            @php
                $frenos = $checklist->tires_and_brakes ?? [];
                $equipo = $checklist->safety_kit ?? [];
                $kFre = array_keys($frenos);
                $kEqu = array_keys($equipo);
                $max2 = max(count($kFre), count($kEqu), 1);
            @endphp
            @for($i = 0; $i < $max2; $i++)
                <tr>
                    <td>{{ isset($kFre[$i]) ? ucwords(str_replace('_', ' ', $kFre[$i])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kFre[$i]))
                            @php $val3 = (string)($frenos[$kFre[$i]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val3), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val3 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                    <td>{{ isset($kEqu[$i]) ? ucwords(str_replace('_', ' ', $kEqu[$i])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kEqu[$i]))
                            @php $val4 = (string)($equipo[$kEqu[$i]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val4), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val4 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endfor

            <!-- Cabina -->
            <tr>
                <td colspan="4" class="section-header">5. Cabina, Cinturones y Accesorios</td>
            </tr>
            @php
                $cabina = $checklist->cabin_and_belts ?? [];
                $kCab = array_keys($cabina);
            @endphp
            @for($i = 0; $i < count($kCab); $i += 2)
                <tr>
                    <td>{{ isset($kCab[$i]) ? ucwords(str_replace('_', ' ', $kCab[$i])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kCab[$i]))
                            @php $val5 = (string)($cabina[$kCab[$i]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val5), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val5 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                    <td>{{ isset($kCab[$i+1]) ? ucwords(str_replace('_', ' ', $kCab[$i+1])) : '-' }}</td>
                    <td style="text-align: center;">
                        @if(isset($kCab[$i+1]))
                            @php $val6 = (string)($cabina[$kCab[$i+1]] ?? ''); @endphp
                            <span class="badge {{ in_array(strtoupper($val6), ['OK', 'BUENO', 'NORMAL', '1', 'SI', 'CORRECTO']) ? 'badge-success' : 'badge-danger' }}">
                                {{ $val6 ?: 'OK' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Observaciones -->
    <table>
        <tr>
            <th style="width: 25%;">Observaciones / Novedades:</th>
            <td>{{ $checklist->observations ?: 'Sin observaciones registradas. Vehículo en condiciones operativas óptimas.' }}</td>
        </tr>
    </table>

    <!-- Firmas -->
    <table class="signatures-table" style="margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                @if($checklist->driver_signature)
                    <div style="margin-bottom: 5px;">
                        @if(str_starts_with($checklist->driver_signature, 'data:image'))
                            <img src="{{ $checklist->driver_signature }}" style="max-height: 40px;" alt="Firma">
                        @else
                            <div style="font-size: 7pt; color: #64748b; font-style: italic;">[Firma Digital Almacenada]</div>
                        @endif
                    </div>
                @endif
                <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 3px;">
                    <b>{{ $checklist->driver?->name ?? 'Conductor' }}</b><br>
                    C.C. {{ $checklist->driver?->identification_number ?? 'S/N' }}<br>
                    <span style="font-size: 6.5pt; color: #64748b;">Firma Conductor Inspector</span>
                </div>
            </td>
            <td style="width: 50%;">
                <div style="height: 40px;"></div>
                <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 3px;">
                    <b>V° B° Despacho / Coordinador SST</b><br>
                    Departamento de Seguridad Vial y Operaciones<br>
                    <span style="font-size: 6.5pt; color: #64748b;">Aprobación y Verificación en Patio</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
