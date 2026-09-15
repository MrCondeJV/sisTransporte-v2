@extends('layouts.app')

@section('title', 'Hoja de Vida Conductor ' . $conductore->name)

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <!-- Header / Banner del Conductor -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="h-16 w-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-md shadow-blue-500/20 flex-shrink-0">
                {{ mb_strtoupper(mb_substr($conductore->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ $conductore->name }}</h1>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $conductore->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-300' }}">
                        {{ $conductore->status }}
                    </span>
                </div>
                <div class="text-sm font-medium text-slate-500 mt-1">
                    C.C. {{ $conductore->document_number }} • Rol: <strong class="text-slate-800">{{ $conductore->employee_type }}</strong> • {{ $conductore->phone ?? 'Sin teléfono' }}
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('conductores.edit', $conductore->id) }}"
                class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                Editar Datos
            </a>
            <a href="{{ route('conductores.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Vigencia de Licencia y Relaciones Laborales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Licencia -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Licencia de Conducción</span>
                <span class="text-xs px-2 py-0.5 rounded-md font-mono font-bold bg-slate-100 text-slate-700">
                    Cat. {{ $conductore->driver_license_category ?? 'C2' }}
                </span>
            </div>
            <div class="font-mono text-base font-black text-slate-900">
                {{ $conductore->driver_license_number ?? 'Sin registrar' }}
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Vencimiento:</span>
                @php $licStatus = $conductore->license_status; @endphp
                @if($licStatus === 'Vencida')
                    <span class="font-bold text-rose-600">
                        {{ $conductore->driver_license_expiration?->format('d/m/Y') ?? 'N/A' }} (Vencida)
                    </span>
                @elseif($licStatus === 'Por Vencer')
                    <span class="font-bold text-amber-600">
                        {{ $conductore->driver_license_expiration?->format('d/m/Y') ?? 'N/A' }} (Por Vencer)
                    </span>
                @else
                    <span class="font-bold text-emerald-700">
                        {{ $conductore->driver_license_expiration?->format('d/m/Y') ?? 'N/A' }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Vínculo Laboral -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Contrato Laboral</span>
                <span class="text-xs font-semibold text-slate-500">{{ $conductore->contract_type ?? 'Sin tipo' }}</span>
            </div>
            <div class="text-base font-bold text-slate-900 truncate">
                {{ $conductore->contract_number ?? 'Sin contrato' }}
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Aliado / Empresa:</span>
                <span class="font-bold text-slate-800 truncate max-w-[150px]">
                    {{ $conductore->partner?->name ?? 'Planta Propia' }}
                </span>
            </div>
        </div>

        <!-- Vehículo Asignado -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vehículo Asignado</span>
                <span class="text-xs font-semibold text-slate-500">Operación</span>
            </div>
            <div class="text-base font-bold text-slate-900">
                @if($conductore->vehicles->count() > 0)
                    @foreach($conductore->vehicles as $v)
                    <a href="{{ route('vehiculos.show', $v->id) }}" class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-bold hover:bg-amber-100 mr-1">
                        {{ $v->plate }} ({{ $v->brand }})
                    </a>
                    @endforeach
                @else
                    <span class="text-xs text-slate-400 italic">Conductor Rotativo (Sin vehículo fijo)</span>
                @endif
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Email:</span>
                <span class="text-slate-700 truncate max-w-[170px]">{{ $conductore->email ?? 'No registrado' }}</span>
            </div>
        </div>
    </div>

    <!-- Historial de Servicios Asignados -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Órdenes de Servicio Recientes</h3>
                <p class="text-xs text-slate-400 mt-0.5">Últimos despachos y turnos asignados a este conductor.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-xl">
                {{ $conductore->serviceOrders->count() }} Órdenes
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase">
                    <tr>
                        <th class="px-5 py-3">N° Orden</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Origen &rarr; Destino</th>
                        <th class="px-4 py-3">Fecha & Hora</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($conductore->serviceOrders->take(10) as $order)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 font-mono font-bold text-blue-600">
                            <a href="{{ route('ordenes.show', $order->id) }}" class="hover:underline">
                                #{{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $order->client?->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $order->origin }} &rarr; {{ $order->destination }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $order->scheduled_start_time?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $order->status === 'Finalizada' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400 italic">
                            No se registran órdenes de servicio asignadas a este conductor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Historial de Checklists Preoperacionales -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Inspecciones Preoperacionales Realizadas (PESV)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Auditorías diarias diligenciadas antes de iniciar ruta.</p>
            </div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl">
                {{ $conductore->preoperationalChecklists->count() }} Inspecciones
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase">
                    <tr>
                        <th class="px-5 py-3">Acta N°</th>
                        <th class="px-4 py-3">Fecha & Hora</th>
                        <th class="px-4 py-3">Vehículo</th>
                        <th class="px-4 py-3">Odómetro</th>
                        <th class="px-4 py-3 text-center">Resultado</th>
                        <th class="px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($conductore->preoperationalChecklists->take(10) as $chk)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 font-mono font-bold text-slate-900">#{{ str_pad($chk->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $chk->date?->format('d/m/Y') }} — {{ $chk->time }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $chk->vehicle?->plate ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ number_format($chk->mileage, 0) }} Km</td>
                        <td class="px-4 py-3 text-center">
                            @if($chk->is_approved)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aprobado</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Rechazado</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('checklists.show', $chk->id) }}" class="text-blue-600 hover:underline font-semibold">
                                Ver Inspección &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400 italic">
                            No hay checklists preoperacionales registrados para este conductor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
