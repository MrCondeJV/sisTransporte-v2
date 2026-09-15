<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\User;
use App\Services\Security\PermissionRegistrarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagerialReportsAndPermissionsTest extends TestCase
{
    protected Tenant $tenant;

    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['id' => 'empresa_qa'],
            ['name' => 'Transportes QA S.A.S.', 'company_name' => 'Transportes QA S.A.S.']
        );

        tenancy()->initialize($this->tenant);

        PermissionRegistrarService::syncTenantPermissions();

        $this->tenantUser = User::firstOrCreate(
            ['email' => 'admin_test@transporte.test'],
            [
                'name' => 'Admin Test',
                'username' => 'admin_test',
                'password' => Hash::make('secret123'),
                'is_active' => true,
            ]
        );
    }

    /**
     * Prueba: Vista de edición y asignación de permisos por rol.
     */
    public function test_admin_can_view_role_permissions_edit_page(): void
    {
        $this->actingAs($this->tenantUser);

        $role = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);

        $response = $this->get(route('roles.edit', $role->id));

        $response->assertOk();
        $response->assertSee('Configurar Permisos del Rol');
        $response->assertSee($role->name);
        $response->assertSee('Operaciones &amp; Órdenes de Servicio', false);
        $response->assertSee('ordenes.ver');
        $response->assertSee('reportes_gerenciales.ver');
    }

    /**
     * Prueba: Actualizar y sincronizar permisos de un rol.
     */
    public function test_admin_can_sync_role_permissions(): void
    {
        $this->actingAs($this->tenantUser);

        $customRole = Role::firstOrCreate(['name' => 'Despachador Nocturno', 'guard_name' => 'web']);

        $selectedPermissions = [
            'ordenes.ver',
            'ordenes.crear',
            'vehiculos.ver',
            'conductores.ver',
        ];

        $response = $this->put(route('roles.update', $customRole->id), [
            'name' => 'Despachador Nocturno',
            'permissions' => $selectedPermissions,
        ]);

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('success');

        $customRole->refresh();
        $this->assertTrue($customRole->hasPermissionTo('ordenes.ver'));
        $this->assertTrue($customRole->hasPermissionTo('ordenes.crear'));
        $this->assertFalse($customRole->hasPermissionTo('ordenes.eliminar'));
    }

    /**
     * Prueba: Panel Gerencial Principal (KPIs ejecutivos y resumen de ingresos).
     */
    public function test_managerial_dashboard_renders_successfully(): void
    {
        $this->actingAs($this->tenantUser);

        $response = $this->get(route('gerencial.reportes.dashboard'));

        $response->assertOk();
        $response->assertSee('KPIs Estratégicos');
        $response->assertSee('Valores por Servicio Diario, Mensual y Anual');
        $response->assertSee('Kilometraje vs Gasolina Semanal');
        $response->assertSee('Órdenes Completadas Sin Pagar');
        $response->assertSee('Órdenes Completadas Pagadas');
        $response->assertSee('Exportar Gráficos a Excel');
    }

    /**
     * Prueba: Endpoints JSON de Gráficos Gerenciales.
     */
    public function test_managerial_chart_endpoints_return_valid_json(): void
    {
        $this->actingAs($this->tenantUser);

        // 1. Semanal
        $respSemanal = $this->getJson(route('gerencial.reportes.chart-semanal'));
        $respSemanal->assertOk()
            ->assertJsonStructure([
                'success',
                'dias',
                'actual' => ['valores', 'detalles', 'fechas', 'semana', 'rango'],
                'anterior' => ['valores', 'detalles', 'fechas', 'semana', 'rango'],
            ]);

        // 2. Combustible
        $respCombustible = $this->getJson(route('gerencial.reportes.chart-combustible'));
        $respCombustible->assertOk()
            ->assertJsonStructure([
                'success',
                'dias',
                'km',
                'combustible',
                'rango',
            ]);

        // 3. Pagos
        $respPagos = $this->getJson(route('gerencial.reportes.chart-pagos'));
        $respPagos->assertOk()
            ->assertJsonStructure([
                'labels',
                'pagadas',
                'no_pagadas',
            ]);

        // 4. Facturas
        $respFacturas = $this->getJson(route('gerencial.reportes.chart-facturas'));
        $respFacturas->assertOk()
            ->assertJsonStructure([
                'labels',
                'facturadas',
                'no_facturadas',
            ]);

        // 5. Estados
        $respEstados = $this->getJson(route('gerencial.reportes.chart-estados'));
        $respEstados->assertOk()
            ->assertJsonStructure([
                'labels',
                'pendiente',
                'en_progreso',
                'completado',
                'cancelado',
            ]);

        // 6. Rutas
        $respRutas = $this->getJson(route('gerencial.reportes.chart-rutas'));
        $respRutas->assertOk()
            ->assertJsonStructure(['labels', 'data']);

        // 7. Eventos Calendario
        $respCal = $this->getJson(route('gerencial.reportes.chart-calendario'));
        $respCal->assertOk();
    }

    /**
     * Prueba: Subreportes de Cartera y Facturación con Exportación a CSV.
     */
    public function test_managerial_order_subreports(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '901234567-8'],
            ['type' => 'Empresa', 'business_name' => 'Empresa Cartera QA S.A.S.']
        );

        $orderPaid = ServiceOrder::firstOrCreate(
            ['order_number' => 'ODS-GER-PAG-01'],
            [
                'client_id' => $client->id,
                'origin' => 'Bocagrande',
                'destination' => 'Mamonal',
                'scheduled_start_time' => Carbon::now(),
                'scheduled_end_time' => Carbon::now()->addHours(2),
                'fare' => 180000,
                'is_paid' => true,
                'invoice_number' => 'FE-2026-001',
                'status' => 'Finalizada',
            ]
        );

        $orderUnpaid = ServiceOrder::firstOrCreate(
            ['order_number' => 'ODS-GER-NOPAG-01'],
            [
                'client_id' => $client->id,
                'origin' => 'Centro',
                'destination' => 'Aeropuerto',
                'scheduled_start_time' => Carbon::now(),
                'scheduled_end_time' => Carbon::now()->addHours(1),
                'fare' => 120000,
                'is_paid' => false,
                'invoice_number' => null,
                'status' => 'Finalizada',
            ]
        );

        // 1. Órdenes Pagadas
        $respPaid = $this->get(route('gerencial.reportes.ordenes-pagadas'));
        $respPaid->assertOk();
        $respPaid->assertSee('Órdenes Completadas Pagadas');
        $respPaid->assertSee('ODS-GER-PAG-01');

        // Exportación CSV de Pagadas
        $respCsvPaid = $this->get(route('gerencial.reportes.ordenes-pagadas', ['export' => 'csv']));
        $respCsvPaid->assertOk();
        $this->assertStringContainsString('text/csv', $respCsvPaid->headers->get('Content-Type') ?? '');

        // 2. Órdenes Sin Pagar (Cartera)
        $respUnpaid = $this->get(route('gerencial.reportes.ordenes-sin-pagar'));
        $respUnpaid->assertOk();
        $respUnpaid->assertSee('Órdenes Completadas Sin Pagar (Cartera)');
        $respUnpaid->assertSee('ODS-GER-NOPAG-01');

        // 3. Órdenes Facturadas
        $respFact = $this->get(route('gerencial.reportes.ordenes-facturadas'));
        $respFact->assertOk();
        $respFact->assertSee('Órdenes Facturadas');
        $respFact->assertSee('FE-2026-001');

        // 4. Órdenes No Facturadas
        $respNoFact = $this->get(route('gerencial.reportes.ordenes-no-facturadas'));
        $respNoFact->assertOk();
        $respNoFact->assertSee('Órdenes Pendientes de Facturación');
        $respNoFact->assertSee('ODS-GER-NOPAG-01');
    }
}
