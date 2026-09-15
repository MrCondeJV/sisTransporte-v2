@extends('layouts.app')

@section('title', 'Empresas Aliadas y Terceros')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{ modalCrear: false, modalEditar: false, aliadoEdit: {} }">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Directorio & Red de Apoyo</span>
                <span>/</span>
                <span class="text-slate-800">Aliados y Propietarios</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Empresas Aliadas & Terceros</h1>
            <p class="text-sm text-slate-500 mt-1">
                Convenios de colaboración empresarial, propietarios afiliados y empresas de transporte aliadas para FUEC consorciado.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total Aliados:</span>
                <span class="font-bold text-slate-900">{{ $totalAliados }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-emerald-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $aliadosActivos }} Activos
                </span>
            </div>

            <button @click="modalCrear = true"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Aliado
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('aliados.index') }}" class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term ?? '' }}"
                class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Buscar aliado por razón social, NIT o persona de contacto (Enter)...">
        </form>
    </div>

    <!-- Tabla Aliados -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Empresa / Razón Social</th>
                        <th class="px-4 py-3.5">NIT & Contacto</th>
                        <th class="px-4 py-3.5 text-center">Vehículos</th>
                        <th class="px-4 py-3.5 text-center">Conductores</th>
                        <th class="px-4 py-3.5 text-center">Servicios</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($aliados as $a)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <a href="{{ route('aliados.show', $a->id) }}" class="font-bold text-slate-900 hover:text-blue-600 hover:underline">
                                {{ $a->name }}
                            </a>
                            @if($a->address)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $a->address }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-mono text-xs font-semibold text-slate-800">NIT: {{ $a->nit }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $a->contact_person ?? 'Sin contacto' }} • Tel: {{ $a->phone ?? 'N/D' }}</div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $a->vehicles_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $a->employees_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $a->service_orders_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $a->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                                {{ $a->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('aliados.show', $a->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Ver flota y servicios">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <button @click="aliadoEdit = {{ json_encode($a) }}; modalEditar = true"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Editar">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('aliados.destroy', $a->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este aliado comercial?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No hay aliados registrados</h3>
                                <p class="text-xs text-slate-400 mt-1">Registra empresas aliadas o terceros propietarios para vincular flota en convenios de transporte.</p>
                                <button @click="modalCrear = true" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                    + Registrar Primer Aliado
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($aliados->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $aliados->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Crear Aliado -->
    <div x-show="modalCrear" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4" @click.away="modalCrear = false">
            <h3 class="text-lg font-bold text-slate-900">Registrar Nuevo Aliado Comercial</h3>
            <form action="{{ route('aliados.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Razón Social / Nombre:</label>
                    <input type="text" name="name" required placeholder="Ej: Transportes Express del Llano S.A.S."
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">NIT o Cédula:</label>
                    <input type="text" name="nit" required placeholder="900987654-1"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Teléfono:</label>
                        <input type="text" name="phone" placeholder="3109876543" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Email:</label>
                        <input type="email" name="email" placeholder="contacto@aliado.com" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Persona de Contacto / Representante:</label>
                    <input type="text" name="contact_person" placeholder="Pedro Pérez" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Dirección:</label>
                    <input type="text" name="address" placeholder="Cra 15 # 45-20" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Estado:</label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="modalCrear = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Guardar Aliado</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Aliado -->
    <div x-show="modalEditar" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4" @click.away="modalEditar = false">
            <h3 class="text-lg font-bold text-slate-900">Editar Aliado Comercial</h3>
            <form :action="'{{ url('/aliados') }}/' + aliadoEdit.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Razón Social:</label>
                    <input type="text" name="name" required x-model="aliadoEdit.name" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">NIT o Cédula:</label>
                    <input type="text" name="nit" required x-model="aliadoEdit.nit" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Teléfono:</label>
                        <input type="text" name="phone" x-model="aliadoEdit.phone" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Email:</label>
                        <input type="email" name="email" x-model="aliadoEdit.email" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Persona de Contacto:</label>
                    <input type="text" name="contact_person" x-model="aliadoEdit.contact_person" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Estado:</label>
                    <select name="status" x-model="aliadoEdit.status" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="modalEditar = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
