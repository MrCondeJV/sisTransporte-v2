@extends('layouts.app')

@section('title', 'Registro FUEC Digital')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Normatividad & Despachos</span>
                <span>/</span>
                <span class="text-slate-800">FUEC Digital con QR</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">FUEC Digital (Decreto 1079 / MinTransporte)</h1>
            <p class="text-sm text-slate-500 mt-1">
                Historial de Extractos de Contrato emitidos con validación mediante código QR verificable ante autoridades de tránsito.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total Emitidos:</span>
                <span class="font-bold text-slate-900">{{ $totalFuecs }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-emerald-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $fuecsVigentes }} Vigentes
                </span>
            </div>

            <a href="{{ route('ordenes.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Orden & FUEC
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('fuec.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-7 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por número oficial FUEC, placa, cliente o conductor (Enter)...">
            </div>

            <div class="md:col-span-4">
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Estados</option>
                    <option value="Vigente" {{ ($estado ?? '') == 'Vigente' ? 'selected' : '' }}>Vigente</option>
                    <option value="Vencido" {{ ($estado ?? '') == 'Vencido' ? 'selected' : '' }}>Vencido</option>
                    <option value="Anulado" {{ ($estado ?? '') == 'Anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
            </div>

            <div class="md:col-span-1 flex justify-end">
                @if(!empty($term) || !empty($estado))
                <a href="{{ route('fuec.index') }}"
                    class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 p-2.5 rounded-xl transition flex items-center justify-center"
                    title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla FUEC -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">N° FUEC Oficial</th>
                        <th class="px-4 py-3.5">Orden & Cliente</th>
                        <th class="px-4 py-3.5">Vehículo & Conductor</th>
                        <th class="px-4 py-3.5 text-center">Vigencia</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Descargas & QR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($documentos as $doc)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-mono font-bold text-sm text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 inline-block">
                                {{ $doc->fuec_number }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Res: {{ $doc->resolution_number }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-800">
                                #{{ $doc->serviceOrder?->order_number ?? 'S/N' }} - {{ $doc->serviceOrder?->client?->business_name ?? 'Particular' }}
                            </div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">
                                {{ $doc->serviceOrder?->origin }} &rarr; {{ $doc->serviceOrder?->destination }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-0.5 rounded bg-amber-50 text-slate-900 border border-amber-300 font-mono text-xs font-extrabold">
                                {{ $doc->serviceOrder?->vehicle?->plate ?? 'N/A' }}
                            </span>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $doc->serviceOrder?->driver?->first_name }} {{ $doc->serviceOrder?->driver?->last_name }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $doc->issue_date?->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-400">Hasta: {{ $doc->expiration_date?->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $estadoColors = [
                                    'Vigente' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Vencido' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'Anulado' => 'bg-slate-100 text-slate-600 border-slate-300',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $estadoColors[$doc->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                {{ $doc->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ url('/fuec/verify/' . $doc->fuec_number) }}" target="_blank"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-cyan-600 hover:bg-cyan-50 transition" title="Verificar QR público">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </a>

                                <a href="{{ route('tenant.fuec.pdf', $doc->id) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 border border-blue-200 transition">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    PDF Oficial
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay documentos FUEC emitidos</h3>
                                <p class="text-xs text-slate-400 mt-1">Los FUEC se generan automáticamente desde las órdenes de servicio con vehículo y conductor asignados.</p>
                                <a href="{{ route('ordenes.index') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                    Ver Órdenes de Servicio
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentos->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $documentos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
