@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="flex justify-center mb-4">
            <div class="h-16 w-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 text-white">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            sisTransporte
        </h2>
        <p class="mt-2 text-sm text-slate-500">
            Plataforma Integral de Transporte Especial & FUEC Digital
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-100 rounded-3xl sm:px-10 border border-slate-200">
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 p-4 border border-red-200 text-sm text-red-700">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Correo Electrónico
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="{{ old('email', 'admin@sistransporte.com') }}"
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="admin@sistransporte.com">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Contraseña
                    </label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        value="admin123456"
                        class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-600 select-none cursor-pointer">
                        <input id="remember" name="remember" type="checkbox" checked
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                        <span class="ml-2 font-medium">Recordarme</span>
                    </label>
                    <span class="text-slate-400">Credenciales demo cargadas</span>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-md text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Ingresar a la Plataforma
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
