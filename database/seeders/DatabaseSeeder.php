<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear SuperAdmin en base de datos central
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistransporte.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
            ]
        );

        $adminEmpresaCentral = User::firstOrCreate(
            ['email' => 'admin@empresa.com'],
            [
                'name' => 'Admin Empresa',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);
        if (! $admin->hasRole('SuperAdmin')) {
            $admin->assignRole($superAdminRole);
        }
        if (! $adminEmpresaCentral->hasRole('SuperAdmin')) {
            $adminEmpresaCentral->assignRole($superAdminRole);
        }

        $this->command->info('✅ Superadmins centrales creados (admin@sistransporte.com y admin@empresa.com con admin123456)');

        // 2. Crear Empresa Demo si no existe
        $tenant = Tenant::find('empresa1');
        if (! $tenant) {
            $tenant = Tenant::create([
                'id' => 'empresa1',
                'name' => 'Transportes Kemuel S.A.S.',
                'nit' => '901234567-8',
                'email' => 'contacto@transporteskemuel.com',
                'phone' => '3001234567',
                'address' => 'Calle 100 # 15-20, Bogotá',
                'is_active' => true,
            ]);

            $tenant->domains()->create([
                'domain' => 'empresa1.localhost',
            ]);

            $this->command->info("✅ Empresa demo 'empresa1' creada con dominio 'empresa1.localhost'");
        }
    }
}
