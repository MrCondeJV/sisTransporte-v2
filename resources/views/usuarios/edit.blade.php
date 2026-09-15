@extends('layouts.app')

@section('title', 'Editar Usuario ' . $usuario->name)

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('usuarios.index') }}" class="hover:underline">Usuarios del Sistema</a>
                <span>/</span>
                <span class="text-slate-800">Editar Usuario</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Editar Usuario: {{ $usuario->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Modifica la información de acceso, rol o actualiza la contraseña.</p>
        </div>

        <a href="{{ route('usuarios.index') }}"
            class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm space-y-1">
            <p class="font-bold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nombre Completo *</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nombre de Usuario (Login)</label>
                <input type="text" name="username" value="{{ old('username', $usuario->username) }}"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Correo Electrónico *</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Teléfono / Celular</label>
                <input type="text" name="phone" value="{{ old('phone', $usuario->phone) }}"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rol Asignado *</label>
                @php
                    $currentRole = $usuario->roles->first()?->name ?? '';
                @endphp
                <select name="role" required
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 transition">
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ old('role', $currentRole) === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center pt-6">
                <label class="relative flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $usuario->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                        class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    <span class="ml-3 text-sm font-semibold text-slate-700">Usuario Activo</span>
                </label>
            </div>

            <div class="md:col-span-2 pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Cambiar Contraseña (Opcional)</h3>
                <p class="text-xs text-slate-500 mb-4">Deja estos campos en blanco si no deseas modificar la contraseña actual.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nueva Contraseña</label>
                <input type="password" name="password" minlength="6"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition"
                    placeholder="Mínimo 6 caracteres">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" minlength="6"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition"
                    placeholder="Repita la nueva contraseña">
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-3">
            <a href="{{ route('usuarios.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-sm hover:bg-blue-700 transition">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
