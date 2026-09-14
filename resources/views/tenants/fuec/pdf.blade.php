<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FUEC N° {{ $fuec_number }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1a1a1a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .title-container {
            text-align: center;
        }
        .main-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #0f172a;
        }
        .sub-title {
            font-size: 8pt;
            margin: 2px 0 0 0;
            color: #475569;
        }
        .fuec-box {
            border: 2px solid #0f172a;
            background-color: #f8fafc;
            text-align: center;
            padding: 6px;
            border-radius: 4px;
        }
        .fuec-box-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .fuec-box-number {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .company-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #0f172a;
        }
        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 8pt;
            color: #334155;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
        }
        .section-header {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-size: 8pt !important;
            font-weight: bold !important;
            text-transform: uppercase;
            text-align: left;
            padding: 4px 8px !important;
        }
        .label {
            font-weight: bold;
            color: #334155;
            font-size: 7.5pt;
        }
        .value {
            color: #0f172a;
        }
        .qr-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .qr-section td {
            vertical-align: top;
            border: 1px solid #94a3b8;
            padding: 8px;
        }
        .signature-box {
            text-align: center;
            height: 70px;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            width: 80%;
            margin: 40px auto 4px auto;
        }
        .legal-footer {
            font-size: 6.5pt;
            color: #64748b;
            text-align: justify;
            margin-top: 8px;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    <!-- Encabezado Principal -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="main-title">REPÚBLICA DE COLOMBIA</div>
                <div class="main-title" style="font-size: 9pt;">MINISTERIO DE TRANSPORTE</div>
                <div class="sub-title">SUPERINTENDENCIA DE TRANSPORTE</div>
                <div class="sub-title" style="font-weight: bold; color: #0f172a; margin-top: 4px;">
                    FORMATO ÚNICO DE EXTRACTO DEL CONTRATO DEL SERVICIO PÚBLICO DE TRANSPORTE TERRESTRE AUTOMOTOR ESPECIAL (FUEC)
                </div>
            </td>
            <td style="width: 30%;">
                <div class="fuec-box">
                    <div class="fuec-box-label">N° EXTRACTO DE CONTRATO</div>
                    <div class="fuec-box-number">{{ $fuec_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Razón Social Empresa -->
    <div class="company-header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="company-meta">
            NIT: {{ $company['nit'] }} | RESOLUCIÓN DE HABILITACIÓN N°: {{ $resolution_number }}
        </div>
    </div>

    <!-- 1. Datos del Contrato y Contratante -->
    <table class="data-table">
        <tr>
            <th colspan="4" class="section-header">1. DATOS DEL CONTRATO Y CONTRATANTE</th>
        </tr>
        <tr>
            <td style="width: 25%;"><span class="label">CONTRATO N°:</span></td>
            <td style="width: 25%;"><span class="value">{{ $contract['number'] }}</span></td>
            <td style="width: 25%;"><span class="label">TIPO DE SERVICIO:</span></td>
            <td style="width: 25%;"><span class="value">{{ $contract['type'] }}</span></td>
        </tr>
        <tr>
            <td><span class="label">CONTRATANTE:</span></td>
            <td><span class="value">{{ $client['name'] }}</span></td>
            <td><span class="label">NIT / C.C.:</span></td>
            <td><span class="value">{{ $client['document'] }}</span></td>
        </tr>
        <tr>
            <td><span class="label">DIRECCIÓN:</span></td>
            <td><span class="value">{{ $client['address'] ?? 'No Registrada' }}</span></td>
            <td><span class="label">TELÉFONO:</span></td>
            <td><span class="value">{{ $client['phone'] ?? 'No Registrado' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">OBJETO DEL CONTRATO:</span></td>
            <td colspan="3"><span class="value">{{ $contract['object'] }}</span></td>
        </tr>
        <tr>
            <td><span class="label">RESPONSABLE CONTRATANTE:</span></td>
            <td colspan="3"><span class="value">{{ $passenger_contact ?? $client['name'] }}</span></td>
        </tr>
    </table>

    <!-- 2. Ruta y Vigencia del Servicio -->
    <table class="data-table">
        <tr>
            <th colspan="4" class="section-header">2. RUTA, RECORRIDO Y VIGENCIA</th>
        </tr>
        <tr>
            <td style="width: 25%;"><span class="label">ORIGEN:</span></td>
            <td style="width: 25%;"><span class="value">{{ $origin }}</span></td>
            <td style="width: 25%;"><span class="label">DESTINO:</span></td>
            <td style="width: 25%;"><span class="value">{{ $destination }}</span></td>
        </tr>
        <tr>
            <td><span class="label">RECORRIDO / RUTA:</span></td>
            <td colspan="3"><span class="value">{{ $route_name ?? ($origin . ' - ' . $destination) }}</span></td>
        </tr>
        <tr>
            <td><span class="label">FECHA INICIO VIGENCIA:</span></td>
            <td><span class="value"><strong>{{ $issue_date->format('d/m/Y') }}</strong></span></td>
            <td><span class="label">FECHA VENCIMIENTO:</span></td>
            <td><span class="value"><strong>{{ $expiration_date->format('d/m/Y') }}</strong></span></td>
        </tr>
        @if(!empty($partner['name']))
        <tr>
            <td><span class="label">CONVENIO COLABORACIÓN:</span></td>
            <td colspan="3"><span class="value">{{ $partner['name'] }} (NIT: {{ $partner['nit'] }})</span></td>
        </tr>
        @endif
    </table>

    <!-- 3. Características del Vehículo -->
    <table class="data-table">
        <tr>
            <th colspan="6" class="section-header">3. CARACTERÍSTICAS DEL VEHÍCULO</th>
        </tr>
        <tr>
            <th>PLACA</th>
            <th>MODELO</th>
            <th>MARCA</th>
            <th>CLASE</th>
            <th>CAPACIDAD</th>
            <th>N° INTERNO</th>
        </tr>
        <tr style="text-align: center;">
            <td><strong>{{ $vehicle['plate'] }}</strong></td>
            <td>{{ $vehicle['model_year'] }}</td>
            <td>{{ $vehicle['brand'] }}</td>
            <td>{{ $vehicle['type'] }}</td>
            <td>{{ $vehicle['capacity'] }} Pasajeros</td>
            <td>{{ $vehicle['internal_number'] ?? '01' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">TARJETA DE OPERACIÓN:</span></td>
            <td colspan="4"><span class="value">{{ $vehicle['operation_card'] ?? 'Vigente' }} (Vence: {{ $vehicle['operation_card_exp'] ?? 'N/A' }})</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">PÓLIZA CONTRACTUAL (RCC):</span></td>
            <td colspan="4"><span class="value">Vigente hasta {{ $vehicle['contractual_exp'] ?? 'N/A' }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">PÓLIZA EXTRACONTRACTUAL (RCE):</span></td>
            <td colspan="4"><span class="value">Vigente hasta {{ $vehicle['extra_contractual_exp'] ?? 'N/A' }}</span></td>
        </tr>
    </table>

    <!-- 4. Datos de los Conductores -->
    <table class="data-table">
        <tr>
            <th colspan="4" class="section-header">4. DATOS DE LOS CONDUCTORES</th>
        </tr>
        <tr>
            <th>ROL</th>
            <th>NOMBRES Y APELLIDOS</th>
            <th>N° CÉDULA</th>
            <th>N° LICENCIA / VIGENCIA</th>
        </tr>
        <tr>
            <td><strong>CONDUCTOR PRINCIPAL</strong></td>
            <td>{{ $driver['name'] }}</td>
            <td>{{ $driver['document'] }}</td>
            <td>{{ $driver['license_number'] }} (Cat: {{ $driver['license_category'] }}, Vence: {{ $driver['license_exp'] }})</td>
        </tr>
        @if(!empty($support_driver['name']))
        <tr>
            <td><strong>CONDUCTOR DE APOYO</strong></td>
            <td>{{ $support_driver['name'] }}</td>
            <td>{{ $support_driver['document'] }}</td>
            <td>{{ $support_driver['license_number'] }} (Cat: {{ $support_driver['license_category'] }}, Vence: {{ $support_driver['license_exp'] }})</td>
        </tr>
        @endif
    </table>

    <!-- 5. Validación QR y Firmas -->
    <table class="qr-section">
        <tr>
            <td style="width: 32%; text-align: center; vertical-align: middle;">
                <div style="width: 110px; height: 110px; margin: 0 auto;">
                    {!! $qr_svg !!}
                </div>
                <div style="font-size: 6.5pt; color: #475569; margin-top: 4px;">
                    Escanee para validar autenticidad en el portal oficial
                </div>
            </td>
            <td style="width: 68%;">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="font-weight: bold; font-size: 8pt; text-transform: uppercase;">
                        {{ $company['name'] }}
                    </div>
                    <div style="font-size: 7pt; color: #475569;">
                        FIRMA Y SELLO DE LA EMPRESA DE TRANSPORTE
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Pie Legal Normativo -->
    <div class="legal-footer">
        <strong>NOTA LEGAL OBLIGATORIA:</strong> Este documento se expide en cumplimiento del Decreto 1079 de 2015 y la Resolución 1069 de 2015 del Ministerio de Transporte de Colombia. El presente Formato Único de Extracto del Contrato (FUEC) autoriza el tránsito del vehículo y el transporte de los pasajeros exclusivamente en la ruta, recorrido y fechas aquí señaladas. Cualquier enmendadura, tachadura o alteración anula la validez del documento. La empresa certifica que el vehículo y los conductores cumplen con la totalidad de requisitos normativos y pólizas de responsabilidad civil vigentes.
    </div>

</body>
</html>
