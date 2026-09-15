@extends('layouts.app')

@section('title', 'Clientes Corporativos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Comercial</span>
                <span>/</span>
                <span class="text-slate-800">Clientes Corporativos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Clientes Corporativos</h1>
            <p class="text-sm text-slate-500 mt-1">Directorio de clientes corporativos, escolares y de turismo para contratos de transporte.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs font-bold text-slate-700">
                Total Clientes: {{ $totalClientes }}
            </div>
            <a href="{{ route('clientes.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex items-center gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por razón social, NIT o teléfono...">
            </div>
            @if(!empty($term))
            <a href="{{ route('clientes.index') }}" class="text-xs font-bold text-rose-600 px-3 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 transition">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Tabla Clientes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Cliente / Razón Social</th>
                        <th class="px-4 py-3.5">Identificación / NIT</th>
                        <th class="px-4 py-3.5">Contacto & Teléfono</th>
                        <th class="px-4 py-3.5">Dirección</th>
                        <th class="px-4 py-3.5 text-center">Contratos</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($clientes as $c)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $c->display_name }}</div>
                            <div class="text-xs text-slate-400">{{ $c->email ?? 'Sin email' }} ({{ $c->type }})</div>
                        </td>
                        <td class="px-4 py-4 font-mono text-xs font-semibold text-slate-800">
                            {{ $c->document_number }}
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-600">
                            {{ $c->phone ?? 'S/N' }}
                        </td>
                        <td class="px-4 py-4 text-xs text-slate-500">
                            {{ $c->address ?? 'No registrada' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $c->contracts->count() }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $c->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                                {{ $c->status ?? 'Activo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('clientes.show', $c->id) }}"
                                    class="p-1.5 text-slate-500 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Ver Ficha Cliente">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('clientes.edit', $c->id) }}"
                                    class="p-1.5 text-slate-500 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition" title="Editar Cliente">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay clientes registrados</h3>
                                <p class="text-xs text-slate-400 mt-1">Crea clientes corporativos o particulares para asociar contratos de transporte y órdenes de despacho.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $clientes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
