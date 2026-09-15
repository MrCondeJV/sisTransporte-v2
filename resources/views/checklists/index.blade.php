@extends('layouts.app')

@section('title', 'Auditoría de Inspección Preoperacional')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Seguridad Vial & Normatividad</span>
                <span>/</span>
                <span class="text-slate-800">Checklist Preoperacional Diario</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Inspecciones Preoperacionales</h1>
            <p class="text-sm text-slate-500 mt-1">
                Auditoría técnica de seguridad vehicular diaria diligenciada por los conductores antes del inicio de ruta (Resolución 20223040040595 de MinTransporte).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Inspecciones Hoy:</span>
                <span class="font-bold text-slate-900">{{ $totalHoy }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-emerald-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $aprobadosHoy }} Aprobadas
                </span>
            </div>

            <a href="{{ route('portal.conductor') }}" target="_blank"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                Portal Conductor
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('checklists.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-4">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Fecha de Inspección:</label>
                <input type="date" name="fecha" value="{{ $fecha ?? '' }}" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-4">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Vehículo:</label>
                <select name="vehicle_id" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los Vehículos</option>
                    @foreach($vehiculos as $v)
                        <option value="{{ $v->id }}" {{ ($vehiculoId == $v->id) ? 'selected' : '' }}>
                            {{ $v->plate }} — {{ $v->brand }} {{ $v->line }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-400 block mb-1 uppercase">Resultado:</label>
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="1" {{ ($estado === '1') ? 'selected' : '' }}>Aprobado (Apto para operar)</option>
                    <option value="0" {{ ($estado === '0') ? 'selected' : '' }}>Rechazado (Con fallas críticas)</option>
                </select>
            </div>

            <div class="md:col-span-1 flex items-end justify-end">
                @if($fecha || $vehiculoId || $estado !== null)
                    <a href="{{ route('checklists.index') }}" class="p-2 text-slate-400 hover:text-slate-600" title="Limpiar filtros">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Checklists -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Fecha & Hora</th>
                        <th class="px-4 py-3.5">Vehículo & Odómetro</th>
                        <th class="px-4 py-3.5">Conductor Inspector</th>
                        <th class="px-4 py-3.5">Orden Vinculada</th>
                        <th class="px-4 py-3.5 text-center">Firma</th>
                        <th class="px-4 py-3.5 text-center">Veredicto</th>
                        <th class="px-5 py-3.5 text-right">Acta & Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($checklists as $chk)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ $chk->date?->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $chk->time }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-extrabold">
                                {{ $chk->vehicle?->plate ?? 'N/A' }}
                            </span>
                            <div class="text-xs text-slate-600 mt-1 font-mono">
                                {{ number_format($chk->mileage, 0) }} km
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-800">{{ $chk->driver?->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">Lic: {{ $chk->driver?->driver_license_number ?? 'S/N' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            @if($chk->serviceOrder)
                                <a href="{{ route('ordenes.show', $chk->service_order_id) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                    #{{ $chk->serviceOrder->order_number }}
                                </a>
                                <div class="text-xs text-slate-400 truncate max-w-xs">{{ $chk->serviceOrder->origin }} &rarr; {{ $chk->serviceOrder->destination }}</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Inspección general de turno</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($chk->driver_signature)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Firmado Digital
                                </span>
                            @else
                                <span class="text-xs text-slate-300">Sin firma</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($chk->is_approved)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Aprobado
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                    Rechazado
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('checklists.show', $chk->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Ver detalle de inspección">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('checklists.pdf', $chk->id) }}" target="_blank"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold border border-slate-300 transition" title="Descargar Acta PDF">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay inspecciones preoperacionales</h3>
                                <p class="text-xs text-slate-400 mt-1">Los registros preoperacionales se generan desde el portal móvil del conductor antes de iniciar cada jornada.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($checklists->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $checklists->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
