@extends('layouts.app')

@section('title', 'Editar Vehículo ' . $vehiculo->plate)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('vehiculos.index') }}" class="hover:underline">Parque Automotor</a>
                <span>/</span>
                <span class="text-slate-800">Editar Unidad</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Vehículo {{ $vehiculo->plate }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $vehiculo->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                    {{ $vehiculo->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Actualiza los datos técnicos o renueva los documentos y fechas de vencimiento.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('vehiculos.show', $vehiculo->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                Ver Hoja de Vida
            </a>
            <a href="{{ route('vehiculos.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
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

    <form action="{{ route('vehiculos.update', $vehiculo->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Bloque 1: Identificación y Especificaciones Técnicas -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-blue-600 mr-2"></span>
                Identificación & Ficha Mecánica
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Placa *</label>
                    <input type="text" name="plate" value="{{ old('plate', $vehiculo->plate) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número Interno</label>
                    <input type="text" name="internal_number" value="{{ old('internal_number', $vehiculo->internal_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tipo de Vehículo *</label>
                    <select name="vehicle_type" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        @foreach(['Automovil' => 'Automóvil', 'Camioneta' => 'Camioneta', 'Van' => 'Van', 'Microbus' => 'Microbús', 'Buseta' => 'Buseta', 'Bus' => 'Bus'] as $key => $label)
                        <option value="{{ $key }}" {{ old('vehicle_type', $vehiculo->vehicle_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Marca *</label>
                    <input type="text" name="brand" value="{{ old('brand', $vehiculo->brand) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Línea / Referencia</label>
                    <input type="text" name="line" value="{{ old('line', $vehiculo->line) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Modelo (Año) *</label>
                    <input type="number" name="model_year" value="{{ old('model_year', $vehiculo->model_year) }}" required min="1990" max="{{ date('Y') + 2 }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Capacidad Pasajeros *</label>
                    <input type="number" name="passenger_capacity" value="{{ old('passenger_capacity', $vehiculo->passenger_capacity) }}" required min="1"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kilometraje Actual (Km)</label>
                    <input type="number" step="0.01" name="current_mileage" value="{{ old('current_mileage', $vehiculo->current_mileage) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Estado Operativo *</label>
                    <select name="status" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Activo" {{ old('status', $vehiculo->status) == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Mantenimiento" {{ old('status', $vehiculo->status) == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        <option value="Inactivo" {{ old('status', $vehiculo->status) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bloque 2: Vencimiento de Documentos Obligatorios -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-emerald-600 mr-2"></span>
                Documentos Obligatorios & Semáforo de Vigencia
            </h2>

            <!-- SOAT -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número de SOAT</label>
                    <input type="text" name="soat_number" value="{{ old('soat_number', $vehiculo->soat_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha Vencimiento SOAT</label>
                    <input type="date" name="soat_expiration" value="{{ old('soat_expiration', $vehiculo->soat_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <!-- Tecnomecánica -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número Certificado RTM</label>
                    <input type="text" name="technomechanical_number" value="{{ old('technomechanical_number', $vehiculo->technomechanical_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha Vencimiento RTM</label>
                    <input type="date" name="technomechanical_expiration" value="{{ old('technomechanical_expiration', $vehiculo->technomechanical_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <!-- Tarjeta de Operación -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Número Tarjeta de Operación</label>
                    <input type="text" name="operation_card_number" value="{{ old('operation_card_number', $vehiculo->operation_card_number) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha Vencimiento Tarjeta Operación</label>
                    <input type="date" name="operation_card_expiration" value="{{ old('operation_card_expiration', $vehiculo->operation_card_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <!-- Póliza Contractual y Extracontractual -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vencimiento Póliza Contractual</label>
                    <input type="date" name="contractual_policy_expiration" value="{{ old('contractual_policy_expiration', $vehiculo->contractual_policy_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vencimiento Póliza Extracontractual</label>
                    <input type="date" name="extra_contractual_policy_expiration" value="{{ old('extra_contractual_policy_expiration', $vehiculo->extra_contractual_policy_expiration?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Bloque 3: Asignación y Propiedad -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-indigo-600 mr-2"></span>
                Asignación & Propietario
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Conductor Habitual</label>
                    <select name="default_driver_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="">-- Sin asignar --</option>
                        @foreach($conductores as $c)
                        <option value="{{ $c->id }}" {{ old('default_driver_id', $vehiculo->default_driver_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Lic: {{ $c->driver_license_number ?? 'S/N' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Propietario / Aliado Flota</label>
                    <select name="partner_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="">-- Empresa Propia / Sin Aliado --</option>
                        @foreach($aliados as $a)
                        <option value="{{ $a->id }}" {{ old('partner_id', $vehiculo->partner_id) == $a->id ? 'selected' : '' }}>
                            {{ $a->name }} (NIT: {{ $a->nit ?? 'S/N' }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="if(confirm('¿Seguro que deseas eliminar este vehículo?')) document.getElementById('delete-form').submit();"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                Eliminar Vehículo
            </button>

            <div class="flex items-center space-x-3">
                <a href="{{ route('vehiculos.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('vehiculos.destroy', $vehiculo->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
