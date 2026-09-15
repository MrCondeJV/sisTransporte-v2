@extends('layouts.app')

@section('title', 'Editar Conductor ' . $conductore->name)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('conductores.index') }}" class="hover:underline">Padrón de Conductores</a>
                <span>/</span>
                <span class="text-slate-800">Editar Conductor</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $conductore->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $conductore->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-300' }}">
                    {{ $conductore->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Actualiza los datos personales, de contrato o renueva la licencia de conducción.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('conductores.show', $conductore->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                Ver Hoja de Vida
            </a>
            <a href="{{ route('conductores.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Volver
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="rounded-2xl bg-red-50 p-4 border border-red-200 text-sm text-red-700">
        <div class="font-bold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('conductores.update', $conductore->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Datos Personales -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-blue-600 mr-2"></span>
                Identificación & Datos Personales
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nombre Completo *</label>
                    <input type="text" name="name" value="{{ old('name', $conductore->name) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cédula de Ciudadanía *</label>
                    <input type="text" name="document_number" value="{{ old('document_number', $conductore->document_number) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Teléfono / Móvil</label>
                    <input type="text" name="phone" value="{{ old('phone', $conductore->phone) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $conductore->email) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Rol / Tipo *</label>
                    <select name="employee_type" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Conductor" {{ old('employee_type', $conductore->employee_type) == 'Conductor' ? 'selected' : '' }}>Conductor</option>
                        <option value="Administrativo" {{ old('employee_type', $conductore->employee_type) == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                        <option value="Operativo" {{ old('employee_type', $conductore->employee_type) == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Dirección de Residencia</label>
                <input type="text" name="address" value="{{ old('address', $conductore->address) }}"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Empresa Aliada / Proveedor</label>
                <select name="partner_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                    <option value="">-- Planta Propia / Sin Aliado --</option>
                    @foreach($aliados as $a)
                    <option value="{{ $a->id }}" {{ old('partner_id', $conductore->partner_id) == $a->id ? 'selected' : '' }}>
                        {{ $a->name }} (NIT: {{ $a->nit ?? 'S/N' }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Licencia de Conducción -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-emerald-600 mr-2"></span>
                Licencia de Conducción (RUNT)
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número de Licencia</label>
                    <input type="text" name="driver_license_number" value="{{ old('driver_license_number', $conductore->driver_license_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Categoría</label>
                    <select name="driver_license_category" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="">-- Seleccionar --</option>
                        @foreach(['C2', 'C3', 'B1', 'B2', 'A1'] as $cat)
                        <option value="{{ $cat }}" {{ old('driver_license_category', $conductore->driver_license_category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="driver_license_expiration" value="{{ old('driver_license_expiration', $conductore->driver_license_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Contrato & Estado -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-indigo-600 mr-2"></span>
                Vínculo Laboral & Estado
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número de Contrato</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', $conductore->contract_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tipo de Contrato</label>
                    <select name="contract_type" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="">-- Seleccionar --</option>
                        <option value="Termino Fijo" {{ old('contract_type', $conductore->contract_type) == 'Termino Fijo' ? 'selected' : '' }}>Término Fijo</option>
                        <option value="Termino Indefinido" {{ old('contract_type', $conductore->contract_type) == 'Termino Indefinido' ? 'selected' : '' }}>Término Indefinido</option>
                        <option value="Prestacion Servicios" {{ old('contract_type', $conductore->contract_type) == 'Prestacion Servicios' ? 'selected' : '' }}>Prestación de Servicios</option>
                        <option value="Otro" {{ old('contract_type', $conductore->contract_type) == 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Estado Operativo *</label>
                    <select name="status" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Activo" {{ old('status', $conductore->status) == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Inactivo" {{ old('status', $conductore->status) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="if(confirm('¿Seguro que deseas dar de baja a este conductor?')) document.getElementById('delete-conductor-form').submit();"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                Eliminar Conductor
            </button>

            <div class="flex items-center space-x-3">
                <a href="{{ route('conductores.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-conductor-form" action="{{ route('conductores.destroy', $conductore->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
