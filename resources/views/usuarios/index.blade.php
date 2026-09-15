@extends('layouts.app')

@section('title', 'Usuarios del Sistema')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Seguridad & Acceso</span>
                <span>/</span>
                <span class="text-slate-800">Usuarios del Sistema</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Usuarios del Sistema</h1>
            <p class="text-sm text-slate-500 mt-1">Administración de cuentas de acceso, roles asignados y credenciales de la empresa.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total:</span>
                <span class="font-bold text-slate-900">{{ $totalUsuarios }}</span>
                <span class="text-slate-300">|</span>
                <span class="text-emerald-600 font-bold">{{ $usuariosActivos }} Activos</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-400 font-semibold">{{ $usuariosInactivos }} Inactivos</span>
            </div>

            <a href="{{ route('roles.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Roles & Permisos
            </a>

            <a href="{{ route('usuarios.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Usuario
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('usuarios.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <div class="md:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Buscar por nombre, usuario, email o teléfono...">
            </div>

            <div class="md:col-span-3">
                <select name="rol" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ ($rol ?? '') === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <select name="estado" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Todos los Estados</option>
                    <option value="1" {{ ($estado ?? '') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ ($estado ?? '') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>

            <div class="md:col-span-1 flex justify-end">
                @if(!empty($term) || !empty($rol) || ($estado !== null && $estado !== ''))
                <a href="{{ route('usuarios.index') }}"
                    class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 p-2.5 rounded-xl transition flex items-center justify-center"
                    title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Usuario</th>
                        <th class="px-4 py-3.5">Contacto</th>
                        <th class="px-4 py-3.5 text-center">Rol Asignado</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-4 py-3.5 text-center">Fecha Registro</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($usuarios as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ '@' . ($u->username ?? strstr($u->email, '@', true)) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs font-medium text-slate-700">{{ $u->email }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $u->phone ?? 'Sin teléfono' }}</div>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @php
                                $roleName = $u->roles->first()?->name ?? 'Sin Rol';
                                $roleClasses = [
                                    'Administrador' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Supervisor' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Conductor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Aliado' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Cliente' => 'bg-purple-50 text-purple-700 border-purple-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleClasses[$roleName] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                {{ $roleName }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($u->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-xs text-slate-500 whitespace-nowrap">
                            {{ $u->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-1">
                                <form action="{{ route('usuarios.toggle-status', $u->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition"
                                        title="{{ $u->is_active ? 'Inactivar acceso' : 'Activar acceso' }}">
                                        @if($u->is_active)
                                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                </form>

                                <a href="{{ route('usuarios.edit', $u->id) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Editar Usuario">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                @if($u->id !== auth()->id())
                                <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('¿Está seguro de eliminar al usuario {{ $u->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar Usuario">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No se encontraron usuarios</h3>
                                <p class="text-xs text-slate-400 mt-1">Crea nuevos usuarios del sistema o ajusta los filtros de búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
