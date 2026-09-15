@extends('layouts.app')

@section('title', 'Editar Cliente ' . $cliente->display_name)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6" x-data="{ clientType: '{{ old('type', $cliente->type) }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a>
                <span>/</span>
                <span class="text-slate-800">Editar Cliente</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $cliente->display_name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $cliente->status === 'Activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-300' }}">
                    {{ $cliente->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Actualiza los datos fiscales, de contacto y estado comercial.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('clientes.show', $cliente->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                Ver Ficha
            </a>
            <a href="{{ route('clientes.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
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

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tipo de Cliente -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-blue-600 mr-2"></span>
                Modalidad & Tipo de Persona
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tipo de Cliente *</label>
                    <select name="type" x-model="clientType" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Empresa">Empresa / Persona Jurídica</option>
                        <option value="Persona Natural">Persona Natural</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">NIT / Cédula *</label>
                    <input type="text" name="document_number" value="{{ old('document_number', $cliente->document_number) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Campos Empresa -->
            <div x-show="clientType === 'Empresa'" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Razón Social *</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $cliente->business_name) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <!-- Campos Persona Natural -->
            <div x-show="clientType === 'Persona Natural'" class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="display: none;">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nombres *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $cliente->first_name) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Apellidos *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $cliente->last_name) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <!-- Contacto y Ubicación -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-emerald-600 mr-2"></span>
                Información de Contacto & Facturación
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Teléfono / Celular</label>
                    <input type="text" name="phone" value="{{ old('phone', $cliente->phone) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Dirección Comercial</label>
                    <input type="text" name="address" value="{{ old('address', $cliente->address) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Estado Operativo *</label>
                    <select name="status" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm">
                        <option value="Activo" {{ old('status', $cliente->status) == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Inactivo" {{ old('status', $cliente->status) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="if(confirm('¿Seguro que deseas eliminar este cliente?')) document.getElementById('delete-client-form').submit();"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                Eliminar Cliente
            </button>

            <div class="flex items-center space-x-3">
                <a href="{{ route('clientes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-client-form" action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
