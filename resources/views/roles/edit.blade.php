@extends('layouts.app')

@section('title', 'Configurar Permisos del Rol')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="rolePermissionsManager()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition">Roles & Permisos</a>
                <span>/</span>
                <span class="text-slate-800">Editar Permisos</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Rol: <span class="text-blue-600">{{ $role->name }}</span>
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border bg-blue-50 text-blue-700 border-blue-200">
                    <span x-text="selectedCount"></span> Permisos Asignados
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Configuración granular de privilegios y accesos a los módulos del sistema de transporte.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Volver al Listado
            </a>

            <button type="submit" form="rolePermissionsForm"
                class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Guardar Permisos
            </button>
        </div>
    </div>

    <!-- Barra de Búsqueda y Acciones Rápidas -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" x-model="searchQuery" placeholder="Buscar permiso o módulo..."
                class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="button" @click="selectAll()"
                class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center">
                <svg class="h-4 w-4 mr-1.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Marcar Todos
            </button>
            <button type="button" @click="deselectAll()"
                class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center">
                <svg class="h-4 w-4 mr-1.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Desmarcar Todos
            </button>
        </div>
    </div>

    <!-- Formulario de Permisos -->
    <form id="rolePermissionsForm" action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Datos Básicos del Rol -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="max-w-md">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nombre del Rol *</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                        @if(in_array($role->name, ['Administrador', 'Supervisor', 'Conductor', 'Cliente', 'Aliado'])) readonly class="w-full px-4 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-semibold cursor-not-allowed" @else class="w-full px-4 py-2.5 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" @endif />
                    @if(in_array($role->name, ['Administrador', 'Supervisor', 'Conductor', 'Cliente', 'Aliado']))
                        <p class="text-[11px] text-slate-500 mt-1">Este es un rol protegido del sistema; su nombre no puede ser modificado pero sus permisos sí son editables.</p>
                    @endif
                </div>
            </div>

            <!-- Módulos y Permisos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($modules as $moduleName => $permissions)
                    @php
                        $moduleKey = Str::slug($moduleName);
                    @endphp
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col"
                         x-show="moduleMatchesSearch('{{ addslashes($moduleName) }}', {{ json_encode(array_keys($permissions)) }})">
                        <!-- Cabecera del Módulo -->
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">{{ $moduleName }}</h3>
                                <p class="text-xs text-slate-500">{{ count($permissions) }} permisos disponibles</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="toggleModule('{{ $moduleKey }}', true)"
                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg transition">
                                    Todos
                                </button>
                                <button type="button" @click="toggleModule('{{ $moduleKey }}', false)"
                                    class="text-[11px] font-bold text-slate-600 hover:text-slate-800 bg-slate-200/70 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition">
                                    Ninguno
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Permisos del Módulo -->
                        <div class="p-6 space-y-4 flex-1">
                            @foreach($permissions as $permName => $permDesc)
                                <label class="flex items-start p-3 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition cursor-pointer"
                                       x-show="permissionMatchesSearch('{{ addslashes($permName) }}', '{{ addslashes($permDesc) }}')">
                                    <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                        data-module="{{ $moduleKey }}"
                                        x-model="permissionsState['{{ $permName }}']"
                                        @change="updateSelectedCount()"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3 text-sm">
                                        <div class="font-mono text-xs font-bold text-slate-800">{{ $permName }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $permDesc }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky Footer de Guardado -->
        <div class="sticky bottom-6 mt-8 z-20">
            <div class="bg-slate-900/90 backdrop-blur-md text-white rounded-2xl p-4 shadow-2xl border border-slate-800 flex items-center justify-between max-w-4xl mx-auto">
                <div class="flex items-center space-x-3 text-sm">
                    <span class="h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Modificando permisos de: <strong class="text-blue-300">{{ $role->name }}</strong></span>
                    <span class="text-slate-400">|</span>
                    <span class="text-slate-300"><strong class="text-white" x-text="selectedCount"></strong> permisos seleccionados</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-white transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2 text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-lg transition">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function rolePermissionsManager() {
    return {
        searchQuery: '',
        permissionsState: {
            @foreach($modules as $permissions)
                @foreach($permissions as $pName => $desc)
                    '{{ $pName }}': {{ in_array($pName, $assignedPermissions) ? 'true' : 'false' }},
                @endforeach
            @endforeach
        },
        selectedCount: {{ count($assignedPermissions) }},

        updateSelectedCount() {
            let count = 0;
            for (const key in this.permissionsState) {
                if (this.permissionsState[key]) count++;
            }
            this.selectedCount = count;
        },

        selectAll() {
            for (const key in this.permissionsState) {
                this.permissionsState[key] = true;
            }
            this.updateSelectedCount();
        },

        deselectAll() {
            for (const key in this.permissionsState) {
                this.permissionsState[key] = false;
            }
            this.updateSelectedCount();
        },

        toggleModule(moduleKey, state) {
            const checkboxes = document.querySelectorAll(`input[data-module="${moduleKey}"]`);
            checkboxes.forEach(cb => {
                this.permissionsState[cb.value] = state;
            });
            this.updateSelectedCount();
        },

        permissionMatchesSearch(name, desc) {
            if (!this.searchQuery.trim()) return true;
            const q = this.searchQuery.toLowerCase();
            return name.toLowerCase().includes(q) || desc.toLowerCase().includes(q);
        },

        moduleMatchesSearch(moduleName, permNames) {
            if (!this.searchQuery.trim()) return true;
            const q = this.searchQuery.toLowerCase();
            if (moduleName.toLowerCase().includes(q)) return true;
            return permNames.some(p => p.toLowerCase().includes(q));
        }
    };
}
</script>
@endsection
