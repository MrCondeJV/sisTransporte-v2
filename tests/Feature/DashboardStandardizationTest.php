<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardStandardizationTest extends TestCase
{
    public function test_admin_dashboard_renders_successfully(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@sistransporte.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin123456'),
                'status' => 'Activo',
                'role' => 'superadmin',
            ]
        );

        $response = $this->actingAs($superAdmin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Plataforma Central sisTransporte');
        $response->assertSee('SuperAdmin SaaS');
    }

    public function test_app_company_dashboard_renders_with_syspos_style(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            ['company_name' => 'Transportes Demo S.A.S.', 'name' => 'Transportes Demo S.A.S.']
        );

        tenancy()->initialize($tenant);

        $tenantUser = User::firstOrCreate(
            ['email' => 'admin@empresa.com'],
            [
                'name' => 'Admin Empresa',
                'password' => Hash::make('admin123456'),
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($tenantUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Alertas de Flota');
        $response->assertSee('Flota Operativa');
    }

    public function test_fuec_resource_index_renders_without_missing_action_class(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            ['company_name' => 'Transportes Demo S.A.S.', 'name' => 'Transportes Demo S.A.S.']
        );

        tenancy()->initialize($tenant);

        $tenantUser = User::firstOrCreate(
            ['email' => 'admin@empresa.com'],
            [
                'name' => 'Admin Empresa',
                'password' => Hash::make('admin123456'),
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($tenantUser)->get('/fuec');
        $response->assertStatus(200);
        $response->assertSee('FUEC Digital');
    }
}
