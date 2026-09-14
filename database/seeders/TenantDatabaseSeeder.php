<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Roles del Sistema para la Empresa
        $roles = [
            'Administrador' => 'Control total de la empresa de transporte',
            'Supervisor' => 'Monitoreo de flota, aprobación de servicios y checklist',
            'Conductor' => 'Gestión de servicios asignados, checklist preoperacional y combustible',
            'Aliado' => 'Empresa o propietario aliado que aporta vehículos',
            'Cliente' => 'Empresa o persona que contrata servicios de transporte',
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Crear Administrador Inicial de la Empresa
        $admin = User::firstOrCreate(
            ['email' => 'admin@empresa.com'],
            [
                'name' => 'Administrador Empresa',
                'username' => 'admin_empresa',
                'password' => Hash::make('admin123456'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Administrador');

        // 3. Crear Conductor de prueba
        $driver = User::firstOrCreate(
            ['email' => 'conductor@empresa.com'],
            [
                'name' => 'Juan Conductor Ejemplo',
                'username' => 'conductor1',
                'phone' => '3109876543',
                'password' => Hash::make('conductor123456'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $driver->assignRole('Conductor');
    }
}
