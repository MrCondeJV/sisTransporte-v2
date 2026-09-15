@extends('layouts.app')

@section('title', 'Editar Contrato #' . $contrato->contract_number)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
        <span>Comercial</span>
        <span>/</span>
        <a href="{{ route('contratos.index') }}" class="hover:text-blue-600 transition">Contratos</a>
        <span>/</span>
        <span class="text-slate-800">Editar #{{ $contrato->contract_number }}</span>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5 mb-6 gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Editar Contrato #{{ $contrato->contract_number }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Modifica las condiciones contractuales, vigencia y estado.</p>
            </div>
            <form action="{{ route('contratos.destroy', $contrato->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este contrato? Esta acción lo removerá de las opciones operativas.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                    Eliminar Contrato
                </button>
            </form>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <p class="font-bold mb-1">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('contratos.update', $contrato->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Cliente -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cliente Contratante *</label>
                    <select name="client_id" required class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}" {{ old('client_id', $contrato->client_id) == $cli->id ? 'selected' : '' }}>
                            {{ $cli->display_name }} ({{ $cli->document_number }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Número de Contrato -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Número de Contrato *</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', $contrato->contract_number) }}" required
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Tipo de Contrato -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Modalidad / Tipo *</label>
                    <select name="contract_type" required class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Empresarial" {{ old('contract_type', $contrato->contract_type) === 'Empresarial' ? 'selected' : '' }}>Empresarial (Transporte de Empleados)</option>
                        <option value="Escolar" {{ old('contract_type', $contrato->contract_type) === 'Escolar' ? 'selected' : '' }}>Escolar (Estudiantes e Instituciones)</option>
                        <option value="Turismo" {{ old('contract_type', $contrato->contract_type) === 'Turismo' ? 'selected' : '' }}>Turismo y Recreación</option>
                        <option value="Salud" {{ old('contract_type', $contrato->contract_type) === 'Salud' ? 'selected' : '' }}>Usuarios del Servicio de Salud</option>
                        <option value="Grupo Especifico" {{ old('contract_type', $contrato->contract_type) === 'Grupo Especifico' ? 'selected' : '' }}>Grupo Específico de Usuarios / Ocasional</option>
                    </select>
                </div>

                <!-- Objeto Contractual -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Objeto del Contrato *</label>
                    <textarea name="contract_object" rows="3" required
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('contract_object', $contrato->contract_object) }}</textarea>
                </div>

                <!-- Fecha Inicio -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fecha Inicio *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $contrato->start_date?->format('Y-m-d')) }}" required
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fecha Terminación / Vencimiento *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $contrato->end_date?->format('Y-m-d')) }}" required
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Valor del Contrato -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Valor Total o Estimado ($) *</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $contrato->value) }}" required
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Estado del Contrato *</label>
                    <select name="status" required class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Vigente" {{ old('status', $contrato->status) === 'Vigente' ? 'selected' : '' }}>Vigente</option>
                        <option value="Vencido" {{ old('status', $contrato->status) === 'Vencido' ? 'selected' : '' }}>Vencido</option>
                        <option value="Cancelado" {{ old('status', $contrato->status) === 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('contratos.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                    Actualizar Contrato
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
