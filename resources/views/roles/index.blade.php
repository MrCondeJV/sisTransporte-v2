@extends('layouts.app')

@section('title', 'Roles y Permisos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{ newRoleModal: false }">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Seguridad & Acceso</span>
                <span>/</span>
                <span class="text-slate-800">Roles y Permisos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Roles y Matriz de Acceso</h1>
            <p class="text-sm text-slate-500 mt-1">Perfiles de seguridad RBAC para el control de acceso y autorización del sistema.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Ver Usuarios
            </a>

            <button type="button" @click="newRoleModal = true"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Rol
            </button>
        </div>
    </div>

    <!-- Lista de Roles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $r)
            @php
                $roleBadges = [
                    'Administrador' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'desc' => 'Control total del sistema, configuración de empresa, auditoría y reportes gerenciales.'],
                    'Supervisor' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'desc' => 'Programación de despachos, asignación de flota, emisión de FUEC y supervisión en ruta.'],
                    'Conductor' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'desc' => 'Acceso al portal móvil PWA para inicio de viaje, odómetros, checklist y combustible.'],
                    'Aliado' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'desc' => 'Acceso al portal de aliados estratégicos y propietarios de vehículos en convenio.'],
                    'Cliente' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'desc' => 'Consulta de viajes programados, verificación de planillas FUEC y estado de cuentas.'],
                ];
                $badge = $roleBadges[$r->name] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'desc' => 'Rol personalizado con permisos operativos asignables.'];
                $isSystem = in_array($r->name, ['Administrador', 'Supervisor', 'Conductor', 'Aliado', 'Cliente']);
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                            {{ $r->name }}
                        </span>
                        @if($isSystem)
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sistema</span>
                        @else
                            <form action="{{ route('roles.destroy', $r->id) }}" method="POST"
                                onsubmit="return confirm('¿Está seguro de eliminar este rol personalizado?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1" title="Eliminar Rol">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $badge['desc'] }}
                    </p>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Permisos activos:</span>
                    <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded-full">
                        {{ $r->permissions->count() }} permisos
                    </span>
                </div>

                <div class="pt-3 flex items-center justify-between">
                    <a href="{{ route('usuarios.index', ['rol' => $r->name]) }}"
                        class="text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
                        {{ $r->users_count }} usuario(s) &rarr;
                    </a>

                    <a href="{{ route('roles.edit', $r->id) }}"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl shadow-2xs transition">
                        <svg class="h-3.5 w-3.5 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Configurar Permisos
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Nuevo Rol -->
    <div x-show="newRoleModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="newRoleModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-extrabold text-slate-900">Registrar Nuevo Rol</h3>
                    <button type="button" @click="newRoleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('roles.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre del Rol *</label>
                        <input type="text" name="name" required placeholder="Ej. Despachador, Auditor, etc."
                            class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="newRoleModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                            Guardar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
