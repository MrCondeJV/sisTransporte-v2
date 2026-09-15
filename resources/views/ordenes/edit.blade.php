@extends('layouts.app')

@section('title', 'Editar Orden de Servicio #' . $ordene->order_number)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('ordenes.index') }}" class="hover:underline">Operaciones</a>
                <span>/</span>
                <a href="{{ route('ordenes.show', $ordene->id) }}" class="hover:underline">#{{ $ordene->order_number }}</a>
                <span>/</span>
                <span class="text-slate-800">Editar</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Editar Orden #{{ $ordene->order_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Actualiza ruta, pasajeros, asignación vehicular y tripulación.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('ordenes.destroy', $ordene->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de anular esta orden de servicio?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                    Eliminar Orden
                </button>
            </form>
            <a href="{{ route('ordenes.show', $ordene->id) }}" class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">
                Cancelar
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="rounded-2xl bg-rose-50 p-4 border border-rose-200 text-sm text-rose-700">
        <div class="font-bold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('ordenes.update', $ordene->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Bloque 1: Cliente y Contrato -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-blue-600 mr-2"></span>
                Cliente & Marco Contractual
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cliente Solicitante *</label>
                    <select name="client_id" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $ordene->client_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->display_name }} (NIT: {{ $c->document_number }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Contrato Vigente</label>
                    <select name="contract_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Servicio Ocasional / Sin contrato marco --</option>
                        @foreach($contratos as $con)
                        <option value="{{ $con->id }}" {{ old('contract_id', $ordene->contract_id) == $con->id ? 'selected' : '' }}>
                            Contrato #{{ $con->contract_number }} - {{ $con->contract_type }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Bloque 2: Ruta y Horarios -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5"
            x-data="{
                origin: '{{ old('origin', $ordene->origin) }}',
                destination: '{{ old('destination', $ordene->destination) }}',
                rutas: {{ json_encode($rutas ?? []) }},
                selectRuta(id) {
                    const r = this.rutas.find(x => x.id == id);
                    if (r) {
                        this.origin = r.origin;
                        this.destination = r.destination;
                    }
                }
            }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3 gap-2">
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <span class="h-2 w-2 rounded-full bg-emerald-600 mr-2"></span>
                    Ruta, Itinerario & Pasajeros
                </h2>
                @if(isset($rutas) && $rutas->isNotEmpty())
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Catálogo:</span>
                    <select @change="selectRuta($event.target.value)"
                        class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-slate-50 text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">Cargar desde Rutas Frecuentes...</option>
                        @foreach($rutas as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->origin }} &rarr; {{ $r->destination }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Origen *</label>
                    <input type="text" name="origin" x-model="origin" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Destino *</label>
                    <input type="text" name="destination" x-model="destination" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha y Hora de Salida *</label>
                    <input type="datetime-local" name="scheduled_start_time"
                        value="{{ old('scheduled_start_time', $ordene->scheduled_start_time?->format('Y-m-d\TH:i')) }}" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Fecha y Hora Llegada Estimada</label>
                    <input type="datetime-local" name="scheduled_end_time"
                        value="{{ old('scheduled_end_time', $ordene->scheduled_end_time?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cantidad de Pasajeros *</label>
                    <input type="number" name="passengers_count" value="{{ old('passengers_count', $ordene->passengers_count) }}" required min="1"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Contacto del Grupo de Pasajeros</label>
                    <input type="text" name="passenger_contact_name" value="{{ old('passenger_contact_name', $ordene->passenger_contact_name) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Teléfono del Contacto</label>
                    <input type="text" name="passenger_contact_phone" value="{{ old('passenger_contact_phone', $ordene->passenger_contact_phone) }}"
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Bloque 3: Asignación Operativa -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                <span class="h-2 w-2 rounded-full bg-indigo-600 mr-2"></span>
                Vehículo & Tripulación Asignada
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vehículo Asignado</label>
                    <select name="vehicle_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Por asignar --</option>
                        @foreach($vehiculos as $v)
                        <option value="{{ $v->id }}" {{ old('vehicle_id', $ordene->vehicle_id) == $v->id ? 'selected' : '' }}>
                            {{ $v->plate }} ({{ $v->brand }} - {{ $v->passenger_capacity }} Pax)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Conductor Titular</label>
                    <select name="driver_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Por asignar --</option>
                        @foreach($conductores as $d)
                        <option value="{{ $d->id }}" {{ old('driver_id', $ordene->driver_id) == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Estado de la Orden *</label>
                    <select name="status" required class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="Pendiente" {{ old('status', $ordene->status) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="Asignada" {{ old('status', $ordene->status) == 'Asignada' ? 'selected' : '' }}>Asignada</option>
                        <option value="En Progreso" {{ old('status', $ordene->status) == 'En Progreso' ? 'selected' : '' }}>En Progreso</option>
                        <option value="Finalizada" {{ old('status', $ordene->status) == 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                        <option value="Cancelada" {{ old('status', $ordene->status) == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Observaciones / Instrucciones Especiales</label>
                <textarea name="service_notes" rows="2"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">{{ old('service_notes', $ordene->service_notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('ordenes.show', $ordene->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">
                Actualizar Orden de Servicio
            </button>
        </div>
    </form>
</div>
@endsection
