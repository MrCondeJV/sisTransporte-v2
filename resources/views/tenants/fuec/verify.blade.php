<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Oficial FUEC - {{ $fuec->fuec_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center font-sans">
    <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        
        <!-- Header con Estado -->
        @if($fuec->status === 'Emitido' && $fuec->expiration_date >= now()->startOfDay())
            <div class="bg-emerald-600 px-6 py-6 text-white text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold uppercase tracking-wider">Documento Auténtico y Vigente</h1>
                <p class="text-emerald-100 text-sm mt-1">Certificado oficial de transporte especial de pasajeros</p>
            </div>
        @elseif($fuec->status === 'Anulado')
            <div class="bg-rose-600 px-6 py-6 text-white text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold uppercase tracking-wider">Documento Anulado</h1>
                <p class="text-rose-100 text-sm mt-1">Este FUEC ha sido cancelado o revocado por la empresa</p>
            </div>
        @else
            <div class="bg-amber-600 px-6 py-6 text-white text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold uppercase tracking-wider">Documento Vencido</h1>
                <p class="text-amber-100 text-sm mt-1">La fecha de vigencia autorizada para este servicio ha expirado</p>
            </div>
        @endif

        <div class="p-6 divide-y divide-slate-100">
            <!-- Bloque N° FUEC -->
            <div class="pb-4 text-center">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Número Oficial FUEC</span>
                <div class="text-2xl font-black text-slate-800 tracking-wider mt-1">{{ $fuec->fuec_number }}</div>
                <span class="inline-block bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full mt-2 font-medium">
                    Resolución MinTransporte: {{ $fuec->resolution_number }}
                </span>
            </div>

            <!-- Detalles de Empresa y Contratante -->
            <div class="py-4 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Empresa Emisora:</span>
                    <span class="text-slate-900 font-bold text-right">{{ tenant('name') ?? 'Empresa de Transporte' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">NIT Empresa:</span>
                    <span class="text-slate-800">{{ tenant('nit') ?? '800000000-1' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Cliente / Contratante:</span>
                    <span class="text-slate-900 font-semibold text-right">{{ $fuec->serviceOrder->client->business_name }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Contrato N°:</span>
                    <span class="text-slate-800">{{ $fuec->serviceOrder->contract->contract_number ?? 'Ocasional' }}</span>
                </div>
            </div>

            <!-- Ruta y Operación -->
            <div class="py-4 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Ruta Autorizada:</span>
                    <span class="text-slate-900 font-semibold text-right">{{ $fuec->serviceOrder->origin }} ➔ {{ $fuec->serviceOrder->destination }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Vigencia Desde:</span>
                    <span class="text-slate-800 font-medium">{{ $fuec->issue_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Vigencia Hasta:</span>
                    <span class="text-slate-800 font-bold text-emerald-700">{{ $fuec->expiration_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <!-- Vehículo y Conductor -->
            <div class="py-4 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Vehículo (Placa):</span>
                    <span class="bg-blue-50 text-blue-700 font-mono font-bold px-2 py-0.5 rounded text-sm">
                        {{ $fuec->serviceOrder->vehicle->plate }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Marca / Modelo:</span>
                    <span class="text-slate-800">{{ $fuec->serviceOrder->vehicle->brand }} {{ $fuec->serviceOrder->vehicle->line }} ({{ $fuec->serviceOrder->vehicle->model_year }})</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Conductor:</span>
                    <span class="text-slate-900 font-medium">{{ $fuec->serviceOrder->driver->name }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium">Cédula Conductor:</span>
                    <span class="text-slate-800">{{ $fuec->serviceOrder->driver->document_number }}</span>
                </div>
            </div>

            <!-- Acciones -->
            <div class="pt-4 text-center">
                @if($fuec->pdf_path)
                    <a href="{{ route('tenant.fuec.download', ['fuec_number' => $fuec->fuec_number]) }}" 
                       class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Descargar Formato FUEC Oficial en PDF
                    </a>
                @endif
            </div>

        </div>

        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-center">
            <span class="text-[11px] text-slate-400">
                Sistema de Verificación Oficial sisTransporte v2 • República de Colombia
            </span>
        </div>

    </div>
</body>
</html>
