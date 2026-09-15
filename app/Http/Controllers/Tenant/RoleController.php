<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Security\PermissionRegistrarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Listado de Roles y Matriz de Permisos.
     */
    public function index(): View
    {
        PermissionRegistrarService::syncTenantPermissions();

        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Guardar nuevo rol personalizado.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Rol '{$role->name}' registrado exitosamente.");
    }

    /**
     * Formulario para configurar y editar los permisos del rol.
     */
    public function edit(Role $role): View
    {
        PermissionRegistrarService::syncTenantPermissions();

        $modules = PermissionRegistrarService::getModulePermissions();
        $assignedPermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'modules', 'assignedPermissions'));
    }

    /**
     * Actualizar nombre y permisos del rol.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $protegidos = ['Administrador', 'Supervisor', 'Conductor', 'Cliente', 'Aliado'];
        if (in_array($role->name, $protegidos) && $role->name !== $validated['name']) {
            return back()->with('error', "El nombre del rol '{$role->name}' es estándar del sistema y no puede ser renombrado.");
        }

        $role->update([
            'name' => $validated['name'],
        ]);

        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
            ->with('success', "Permisos del rol '{$role->name}' actualizados exitosamente (".count($permissions).' permisos asignados).');
    }

    /**
     * Eliminar rol (solo si no tiene usuarios asignados).
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', "No se puede eliminar el rol '{$role->name}' porque tiene {$role->users()->count()} usuario(s) asignado(s).");
        }

        $protegidos = ['Administrador', 'Supervisor', 'Conductor', 'Cliente', 'Aliado'];
        if (in_array($role->name, $protegidos)) {
            return back()->with('error', "El rol '{$role->name}' es un rol del sistema y está protegido contra eliminación.");
        }

        $nombre = $role->name;
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol '{$nombre}' eliminado exitosamente.");
    }
}
