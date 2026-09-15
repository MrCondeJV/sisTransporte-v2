<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use App\Models\Tenant\Employee;
use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Maintenance;
use App\Models\Tenant\Partner;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\Route as TenantRoute;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleActionsCrudTest extends TestCase
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
     * Módulo Clientes: Index, Create, Show, Edit.
     */
    public function test_clientes_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900980138-1'],
            [
                'type' => 'Empresa',
                'business_name' => 'Cliente de Prueba SAS',
                'phone' => '3001234567',
                'email' => 'cliente_prueba@test.com',
                'address' => 'Zona Franca Manzana 3',
                'status' => 'Activo',
            ]
        );

        // Index
        $resIndex = $this->get(route('clientes.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Clientes Corporativos');
        $resIndex->assertSee('Cliente de Prueba SAS');

        // Create
        $resCreate = $this->get(route('clientes.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Nuevo Cliente');

        // Show
        $resShow = $this->get(route('clientes.show', $client));
        $resShow->assertStatus(200);
        $resShow->assertSee('Cliente de Prueba SAS');
        $resShow->assertSee('900980138-1');

        // Edit
        $resEdit = $this->get(route('clientes.edit', $client));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Editar Cliente');
    }

    /**
     * Módulo Contratos: Index, Create, Show, Edit.
     */
    public function test_contratos_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '800555444-2'],
            [
                'type' => 'Empresa',
                'business_name' => 'Transportes Marítimos SA',
                'status' => 'Activo',
            ]
        );

        $contract = Contract::firstOrCreate(
            ['contract_number' => 'CT-QA-2026-001'],
            [
                'client_id' => $client->id,
                'contract_object' => 'Transporte de tripulantes de buque',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'value' => 25000000,
                'contract_type' => 'Empresarial',
                'status' => 'Vigente',
            ]
        );

        // Index
        $resIndex = $this->get(route('contratos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Contratos de Transporte');
        $resIndex->assertSee('CT-QA-2026-001');

        // Create
        $resCreate = $this->get(route('contratos.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Suscripción de Contrato de Transporte');

        // Show
        $resShow = $this->get(route('contratos.show', $contract));
        $resShow->assertStatus(200);
        $resShow->assertSee('CT-QA-2026-001');
        $resShow->assertSee('Transporte de tripulantes de buque');

        // Edit
        $resEdit = $this->get(route('contratos.edit', $contract));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Editar Contrato');
    }

    /**
     * Módulo Vehículos: Index, Create, Show, Edit.
     */
    public function test_vehiculos_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'TTX999'],
            [
                'internal_number' => '099',
                'brand' => 'RENAULT',
                'line' => 'MASTER',
                'model_year' => 2024,
                'color' => 'BLANCO',
                'vehicle_type' => 'Van',
                'passenger_capacity' => 16,
                'current_mileage' => 12000,
                'status' => 'Activo',
            ]
        );

        // Index
        $resIndex = $this->get(route('vehiculos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('TTX999');

        // Create
        $resCreate = $this->get(route('vehiculos.create'));
        $resCreate->assertStatus(200);

        // Show
        $resShow = $this->get(route('vehiculos.show', $vehicle));
        $resShow->assertStatus(200);
        $resShow->assertSee('TTX999');
        $resShow->assertSee('RENAULT');

        // Edit
        $resEdit = $this->get(route('vehiculos.edit', $vehicle));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('TTX999');
    }

    /**
     * Módulo Conductores: Index, Create, Show, Edit.
     */
    public function test_conductores_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $employee = Employee::firstOrCreate(
            ['document_number' => 'CC-73555123'],
            [
                'name' => 'Carlos Mendoza Pérez',
                'phone' => '3119876543',
                'email' => 'carlos.mendoza@test.com',
                'employee_type' => 'Conductor',
                'driver_license_number' => '73555123',
                'driver_license_category' => 'C2',
                'status' => 'Activo',
            ]
        );

        // Index
        $resIndex = $this->get(route('conductores.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Carlos Mendoza Pérez');

        // Create
        $resCreate = $this->get(route('conductores.create'));
        $resCreate->assertStatus(200);

        // Show
        $resShow = $this->get(route('conductores.show', $employee));
        $resShow->assertStatus(200);
        $resShow->assertSee('Carlos Mendoza Pérez');

        // Edit
        $resEdit = $this->get(route('conductores.edit', $employee));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Carlos Mendoza Pérez');
    }

    /**
     * Módulo Órdenes de Servicio: Index, Create, Show, Edit.
     */
    public function test_ordenes_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $client = Client::firstOrCreate(
            ['document_number' => '900111222-3'],
            ['type' => 'Empresa', 'business_name' => 'Ecopetrol Refinería', 'status' => 'Activo']
        );

        $order = ServiceOrder::firstOrCreate(
            ['order_number' => 'ODS-QA-999'],
            [
                'client_id' => $client->id,
                'origin' => 'Bocagrande, Hotel Caribe',
                'destination' => 'Mamonal Km 13',
                'scheduled_start_time' => now()->addHour(),
                'scheduled_end_time' => now()->addHours(3),
                'passenger_contact_name' => 'Ing. Roberto Gómez',
                'passengers_count' => 4,
                'status' => 'Pendiente',
            ]
        );

        // Index
        $resIndex = $this->get(route('ordenes.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Órdenes de Servicio');

        $resSearch = $this->get(route('ordenes.index', ['buscar' => 'ODS-QA-999']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('ODS-QA-999');

        // Create
        $resCreate = $this->get(route('ordenes.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Nueva Orden de Servicio');

        // Show
        $resShow = $this->get(route('ordenes.show', $order));
        $resShow->assertStatus(200);
        $resShow->assertSee('ODS-QA-999');
        $resShow->assertSee('Bocagrande, Hotel Caribe');

        // Edit
        $resEdit = $this->get(route('ordenes.edit', $order));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Editar Orden de Servicio');
        $resEdit->assertSee('ODS-QA-999');

        // Duplicar (Clonar)
        $resDuplicate = $this->post(route('ordenes.duplicar', $order));
        $resDuplicate->assertStatus(302);
        $clonedOrder = ServiceOrder::where('origin', 'Bocagrande, Hotel Caribe')
            ->where('destination', 'Mamonal Km 13')
            ->where('id', '!=', $order->id)
            ->first();
        $this->assertNotNull($clonedOrder);
        $this->assertEquals('Pendiente', $clonedOrder->status);
    }

    /**
     * Módulos Combustible y Mantenimientos: Index y Acciones.
     */
    public function test_combustible_y_mantenimientos_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'UAR123'],
            [
                'internal_number' => '050',
                'brand' => 'TOYOTA',
                'line' => 'HIACE',
                'model_year' => 2023,
                'vehicle_type' => 'Van',
                'passenger_capacity' => 14,
                'current_mileage' => 45000,
                'status' => 'Activo',
            ]
        );

        $refill = FuelRefill::create([
            'vehicle_id' => $vehicle->id,
            'refill_date' => now()->toDateString(),
            'gallons' => 12.5,
            'total_cost' => 187500,
            'price_per_gallon' => 15000,
            'odometer_mileage' => 45150,
            'gas_station_name' => 'Terpel Mamonal',
        ]);

        $maintenance = Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => 'Preventivo',
            'maintenance_date' => now()->toDateString(),
            'mileage' => 45000,
            'cost' => 350000,
            'workshop_name' => 'Taller Diesel del Caribe',
            'details' => 'Cambio de aceite 15W40 y filtros de aire y combustible',
        ]);

        // Combustible Index
        $resFuelIndex = $this->get(route('combustible.index'));
        $resFuelIndex->assertStatus(200);
        $resFuelIndex->assertSee('Combustible y Rendimiento');
        $resFuelIndex->assertSee('Terpel Mamonal');

        // Mantenimientos Index
        $resMaintIndex = $this->get(route('mantenimientos.index'));
        $resMaintIndex->assertStatus(200);
        $resMaintIndex->assertSee('Mantenimiento de Flota');
        $resMaintIndex->assertSee('Taller Diesel del Caribe');

        // Delete Fuel Refill
        $resFuelDelete = $this->delete(route('combustible.destroy', $refill));
        $resFuelDelete->assertRedirect();
        $this->assertDatabaseMissing('fuel_refills', ['id' => $refill->id]);

        // Delete Maintenance
        $resMaintDelete = $this->delete(route('mantenimientos.destroy', $maintenance));
        $resMaintDelete->assertRedirect();
        $this->assertDatabaseMissing('maintenances', ['id' => $maintenance->id]);
    }

    /**
     * Módulo Empresas Aliadas: Index, Show, Update, Delete.
     */
    public function test_aliados_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $partner = Partner::firstOrCreate(
            ['nit' => '900888999-1'],
            [
                'name' => 'Transportes del Caribe Aliado SAS',
                'phone' => '3009998877',
                'email' => 'aliado@caribe.com',
                'status' => 'Activo',
            ]
        );

        // Index
        $resIndex = $this->get(route('aliados.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Empresas Aliadas');
        $resIndex->assertSee('Transportes del Caribe Aliado SAS');

        // Show
        $resShow = $this->get(route('aliados.show', $partner));
        $resShow->assertStatus(200);
        $resShow->assertSee('Transportes del Caribe Aliado SAS');

        // Update
        $resUpdate = $this->put(route('aliados.update', $partner), [
            'name' => 'Transportes del Caribe Aliado SAS Renombrado',
            'nit' => '900888999-1',
            'status' => 'Activo',
        ]);
        $resUpdate->assertRedirect();
        $this->assertEquals('Transportes del Caribe Aliado SAS Renombrado', $partner->fresh()->name);

        // Delete
        $resDelete = $this->delete(route('aliados.destroy', $partner));
        $resDelete->assertRedirect();
        $this->assertSoftDeleted('partners', ['id' => $partner->id]);
    }

    /**
     * Módulo Catálogo de Rutas: Index, Store, Update, Delete.
     */
    public function test_rutas_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $ruta = TenantRoute::firstOrCreate(
            ['name' => 'Ruta Cartagenera Mamonal'],
            [
                'origin' => 'Cartagena Centro',
                'destination' => 'Mamonal Refinería',
                'route_type' => 'Empresarial',
                'status' => 'Activa',
            ]
        );

        // Index
        $resIndex = $this->get(route('rutas.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Catálogo Maestro de Rutas');
        $resIndex->assertSee('Ruta Cartagenera Mamonal');

        // Update
        $resUpdate = $this->put(route('rutas.update', $ruta), [
            'name' => 'Ruta Cartagenera Mamonal Exprés',
            'origin' => 'Cartagena Centro',
            'destination' => 'Mamonal Refinería',
            'route_type' => 'Empresarial',
            'status' => 'Activa',
        ]);
        $resUpdate->assertRedirect();
        $this->assertEquals('Ruta Cartagenera Mamonal Exprés', $ruta->fresh()->name);

        // Delete
        $resDelete = $this->delete(route('rutas.destroy', $ruta));
        $resDelete->assertRedirect();
        $this->assertSoftDeleted('routes', ['id' => $ruta->id]);
    }

    /**
     * Módulo Checklists Preoperacionales: Index, Show.
     */
    public function test_checklists_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $vehicle = Vehicle::firstOrCreate(
            ['plate' => 'CHK777'],
            [
                'brand' => 'CHEVROLET',
                'line' => 'N300',
                'model_year' => 2023,
                'vehicle_type' => 'Van',
                'status' => 'Activo',
            ]
        );

        $driver = Employee::firstOrCreate(
            ['document_number' => 'CC-99887766'],
            [
                'name' => 'Juan Conductor Checklist',
                'employee_type' => 'Conductor',
                'status' => 'Activo',
            ]
        );

        $chk = PreoperationalChecklist::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'date' => now()->toDateString(),
            'time' => '07:30:00',
            'mileage' => 25000,
            'fluid_levels' => ['aceite' => 'ok', 'frenos' => 'ok'],
            'lights_and_electrical' => ['altas' => 'ok', 'bajas' => 'ok'],
            'tires_and_brakes' => ['presion' => 'ok'],
            'safety_kit' => ['extintor' => 'ok'],
            'cabin_and_belts' => ['cinturon' => 'ok'],
            'is_approved' => true,
        ]);

        // Index
        $resIndex = $this->get(route('checklists.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Auditoría de Inspección Preoperacional');
        $resIndex->assertSee('CHK777');

        // Show
        $resShow = $this->get(route('checklists.show', $chk));
        $resShow->assertStatus(200);
        $resShow->assertSee('CHK777');
        $resShow->assertSee('Juan Conductor Checklist');
    }

    /**
     * Operaciones CRUD Update y Delete en Vehículos, Conductores, Clientes, Contratos y Órdenes.
     */
    public function test_update_and_delete_operations(): void
    {
        $this->actingAs($this->tenantUser);

        // 1. Cliente Update & Delete
        $client = Client::create([
            'type' => 'Persona Natural',
            'first_name' => 'María',
            'last_name' => 'González',
            'document_number' => 'CC-55443322',
            'status' => 'Activo',
        ]);
        $resClientUpdate = $this->put(route('clientes.update', $client), [
            'type' => 'Persona Natural',
            'first_name' => 'María Elena',
            'last_name' => 'González',
            'document_number' => 'CC-55443322',
            'status' => 'Activo',
        ]);
        $resClientUpdate->assertRedirect();
        $this->assertEquals('María Elena', $client->fresh()->first_name);

        $resClientDelete = $this->delete(route('clientes.destroy', $client));
        $resClientDelete->assertRedirect();
        $this->assertSoftDeleted('clients', ['id' => $client->id]);

        // 2. Contrato Update & Delete
        $client2 = Client::firstOrCreate(
            ['document_number' => '900777111-5'],
            ['type' => 'Empresa', 'business_name' => 'Empresa Contratos CRUD', 'status' => 'Activo']
        );
        $contractNum = 'CT-CRUD-TEMP-'.uniqid();
        $contract = Contract::create([
            'client_id' => $client2->id,
            'contract_number' => $contractNum,
            'contract_object' => 'Transporte temporal para evento',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'value' => 5000000,
            'contract_type' => 'Turismo',
            'status' => 'Vigente',
        ]);
        $resContractUpdate = $this->put(route('contratos.update', $contract), [
            'client_id' => $client2->id,
            'contract_number' => $contractNum,
            'contract_object' => 'Transporte temporal para evento actualizado',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'value' => 7000000,
            'contract_type' => 'Turismo',
            'status' => 'Vigente',
        ]);
        $resContractUpdate->assertRedirect();
        $this->assertEquals('Transporte temporal para evento actualizado', $contract->fresh()->contract_object);

        $resContractDelete = $this->delete(route('contratos.destroy', $contract));
        $resContractDelete->assertRedirect();
        $this->assertSoftDeleted('contracts', ['id' => $contract->id]);

        // 3. Vehículo Update & Delete
        $uniquePlate = 'D'.strtoupper(substr(md5(uniqid()), 0, 5));
        $veh = Vehicle::create([
            'plate' => $uniquePlate,
            'brand' => 'NISSAN',
            'line' => 'URVAN',
            'model_year' => 2022,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 14,
            'status' => 'Activo',
        ]);
        $resVehUpdate = $this->put(route('vehiculos.update', $veh), [
            'plate' => $veh->plate,
            'brand' => 'NISSAN JAPÓN',
            'model_year' => 2022,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 14,
            'status' => 'Activo',
        ]);
        $resVehUpdate->assertRedirect();
        $this->assertEquals('NISSAN JAPÓN', $veh->fresh()->brand);

        $resVehDelete = $this->delete(route('vehiculos.destroy', $veh));
        $resVehDelete->assertRedirect();
        $this->assertSoftDeleted('vehicles', ['id' => $veh->id]);

        // 4. Conductor Update & Delete
        $cond = Employee::create([
            'document_number' => 'CC-'.rand(100000, 999999),
            'name' => 'Conductor Para Eliminar',
            'employee_type' => 'Conductor',
            'status' => 'Activo',
        ]);
        $resCondUpdate = $this->put(route('conductores.update', $cond), [
            'document_number' => $cond->document_number,
            'name' => 'Conductor Renombrado',
            'employee_type' => 'Conductor',
            'status' => 'Activo',
        ]);
        $resCondUpdate->assertRedirect();
        $this->assertEquals('Conductor Renombrado', $cond->fresh()->name);

        $resCondDelete = $this->delete(route('conductores.destroy', $cond));
        $resCondDelete->assertRedirect();
        $this->assertSoftDeleted('employees', ['id' => $cond->id]);

        // 5. Orden de Servicio Update & Delete
        $order = ServiceOrder::create([
            'order_number' => 'ODS-DEL-'.rand(1000, 9999),
            'client_id' => $client2->id,
            'origin' => 'Origen Test',
            'destination' => 'Destino Test',
            'scheduled_start_time' => now()->addDay(),
            'scheduled_end_time' => now()->addDay()->addHours(2),
            'passengers_count' => 2,
            'status' => 'Pendiente',
        ]);
        $resOrderUpdate = $this->put(route('ordenes.update', $order), [
            'client_id' => $client2->id,
            'origin' => 'Origen Test Modificado',
            'destination' => 'Destino Test Modificado',
            'scheduled_start_time' => now()->addDay(),
            'passengers_count' => 4,
            'status' => 'Asignada',
        ]);
        $resOrderUpdate->assertRedirect();
        $this->assertEquals('Origen Test Modificado', $order->fresh()->origin);

        $resOrderDelete = $this->delete(route('ordenes.destroy', $order));
        $resOrderDelete->assertRedirect();
        $this->assertSoftDeleted('service_orders', ['id' => $order->id]);
    }

    /**
     * Módulo Usuarios y Roles: Index, Create, Store, Edit, Update, Toggle y Delete.
     */
    public function test_usuarios_y_roles_module_actions(): void
    {
        $this->actingAs($this->tenantUser);

        $suffix = uniqid();
        $roleName = 'Auditor QA '.$suffix;
        $userName = 'qatest_'.$suffix;
        $userEmail = 'qatest_'.$suffix.'@empresa.com';

        // 1. Roles Index
        $resRoles = $this->get(route('roles.index'));
        $resRoles->assertStatus(200);
        $resRoles->assertSee('Roles y Matriz de Acceso');

        // 2. Crear Rol Personalizado
        $resRoleStore = $this->post(route('roles.store'), [
            'name' => $roleName,
        ]);
        $resRoleStore->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => $roleName]);

        // 3. Usuarios Index
        $resUsersIndex = $this->get(route('usuarios.index'));
        $resUsersIndex->assertStatus(200);
        $resUsersIndex->assertSee('Usuarios del Sistema');

        // 4. Usuarios Create
        $resUserCreate = $this->get(route('usuarios.create'));
        $resUserCreate->assertStatus(200);
        $resUserCreate->assertSee('Registrar Nuevo Usuario');

        // 5. Usuarios Store
        $resUserStore = $this->post(route('usuarios.store'), [
            'name' => 'Usuario QA Test',
            'username' => $userName,
            'email' => $userEmail,
            'phone' => '3009998877',
            'role' => $roleName,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => '1',
        ]);
        $resUserStore->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => $userEmail]);

        $user = User::where('email', $userEmail)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole($roleName));

        // 6. Usuarios Edit
        $resUserEdit = $this->get(route('usuarios.edit', $user));
        $resUserEdit->assertStatus(200);
        $resUserEdit->assertSee('Editar Usuario: Usuario QA Test');

        // 7. Usuarios Update
        $resUserUpdate = $this->put(route('usuarios.update', $user), [
            'name' => 'Usuario QA Actualizado',
            'username' => $userName,
            'email' => $userEmail,
            'phone' => '3001112233',
            'role' => $roleName,
            'is_active' => '1',
        ]);
        $resUserUpdate->assertRedirect(route('usuarios.index'));
        $this->assertEquals('Usuario QA Actualizado', $user->fresh()->name);

        // 8. Toggle Status
        $resToggle = $this->post(route('usuarios.toggle-status', $user));
        $resToggle->assertRedirect();
        $this->assertFalse((bool) $user->fresh()->is_active);

        // 9. Usuarios Destroy
        $resUserDelete = $this->delete(route('usuarios.destroy', $user));
        $resUserDelete->assertRedirect(route('usuarios.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        // 10. Eliminar Rol Personalizado (ahora que no tiene usuarios asignados)
        $role = Role::where('name', $roleName)->first();
        $resRoleDelete = $this->delete(route('roles.destroy', $role));
        $resRoleDelete->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => $roleName]);
    }

    /**
     * Verificar que Route Model Binding resuelve modelos del tenant ({ordene}) en peticiones frescas
     * donde tenancy no estaba pre-inicializado en memoria.
     */
    public function test_route_model_binding_resolves_tenant_order_from_fresh_request(): void
    {
        // 1. Asegurar que existe una orden en el tenant
        $client = Client::firstOrCreate(
            ['document_number' => '900980138-1'],
            ['type' => 'Empresa', 'business_name' => 'Cliente RMB Test SAS']
        );

        $order = ServiceOrder::firstOrCreate(
            ['order_number' => 'ODS-RMB-TEST'],
            [
                'client_id' => $client->id,
                'origin' => 'Cartagena Centro',
                'destination' => 'Mamonal Refineria',
                'scheduled_start_time' => now()->addDay(),
                'scheduled_end_time' => now()->addDay()->addHours(2),
                'passengers_count' => 1,
                'status' => 'Pendiente',
            ]
        );

        $orderId = $order->id;

        // 2. Finalizar Tenancy en memoria para simular una petición HTTP de navegador real
        tenancy()->end();
        $this->assertFalse(tenancy()->initialized);

        // 3. Realizar petición GET /ordenes/{id}
        $response = $this->actingAs($this->tenantUser)
            ->withSession(['active_tenant_id' => 'empresa_qa'])
            ->get(route('ordenes.show', $orderId));

        $response->assertStatus(200);
        $response->assertSee('ODS-RMB-TEST');
        $response->assertSee('Mamonal Refineria');
    }
}
