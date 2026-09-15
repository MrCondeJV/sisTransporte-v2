<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Listado de Usuarios del Sistema.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $rol = $request->input('rol');
        $estado = $request->input('estado');

        $query = User::with('roles')->orderBy('name');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($rol) {
            $query->whereHas('roles', function ($q) use ($rol) {
                $q->where('name', $rol);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('is_active', $estado === '1');
        }

        $totalUsuarios = User::count();
        $usuariosActivos = User::where('is_active', true)->count();
        $usuariosInactivos = $totalUsuarios - $usuariosActivos;

        $usuarios = $query->paginate(12)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('usuarios.index', compact(
            'usuarios',
            'roles',
            'term',
            'rol',
            'estado',
            'totalUsuarios',
            'usuariosActivos',
            'usuariosInactivos'
        ));
    }

    /**
     * Formulario de creación de usuario.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('usuarios.create', compact('roles'));
    }

    /**
     * Almacenar un nuevo usuario.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = $request->boolean('is_active', true);

        // 1. Guardar en base de datos del tenant
        $tenantUser = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $isActive,
            'password' => Hash::make($validated['password']),
        ]);

        $tenantUser->syncRoles([$validated['role']]);

        // 2. Sincronizar en base de datos central para acceso global
        try {
            $centralUser = User::on(config('database.default'))->where('email', $validated['email'])->first();
            if (! $centralUser) {
                User::on(config('database.default'))->create([
                    'name' => $validated['name'],
                    'username' => $validated['username'] ?? null,
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'is_active' => $isActive,
                    'password' => Hash::make($validated['password']),
                ]);
            }
        } catch (\Throwable) {
            // Continuar si central ya contiene el usuario
        }

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$tenantUser->name}' creado exitosamente con el rol '{$validated['role']}'.");
    }

    /**
     * Ver perfil y detalle de usuario.
     */
    public function show(User $usuario): View
    {
        $usuario->load('roles');

        return view('usuarios.show', compact('usuario'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(User $usuario): View
    {
        $usuario->load('roles');
        $roles = Role::orderBy('name')->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($usuario->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $usuario->update($updateData);
        $usuario->syncRoles([$validated['role']]);

        // Sincronizar en base de datos central
        try {
            $centralUser = User::on(config('database.default'))->where('email', $usuario->getOriginal('email'))->first();
            if ($centralUser) {
                $centralData = [
                    'name' => $updateData['name'],
                    'username' => $updateData['username'],
                    'email' => $updateData['email'],
                    'phone' => $updateData['phone'],
                    'is_active' => $updateData['is_active'],
                ];
                if (! empty($validated['password'])) {
                    $centralData['password'] = $updateData['password'];
                }
                $centralUser->update($centralData);
            }
        } catch (\Throwable) {
            // Ignorar fallas secundarias de central
        }

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado satisfactoriamente.");
    }

    /**
     * Cambiar estado activo / inactivo.
     */
    public function toggleStatus(User $usuario): RedirectResponse
    {
        $usuario->update([
            'is_active' => ! $usuario->is_active,
        ]);

        $estadoTxt = $usuario->is_active ? 'activado' : 'inactivado';

        return back()->with('success', "Usuario '{$usuario->name}' {$estadoTxt} exitosamente.");
    }

    /**
     * Eliminar usuario.
     */
    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario de la sesión actual.');
        }

        $nombre = $usuario->name;
        $email = $usuario->email;

        $usuario->delete();

        try {
            User::on(config('database.default'))->where('email', $email)->delete();
        } catch (\Throwable) {
            // Ignorar
        }

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$nombre}' eliminado satisfactoriamente.");
    }
}
