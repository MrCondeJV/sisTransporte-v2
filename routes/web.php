<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\ApprovalRequestController;
use App\Http\Controllers\Tenant\ChecklistController;
use App\Http\Controllers\Tenant\ClientController;
use App\Http\Controllers\Tenant\ContractController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\FuecController;
use App\Http\Controllers\Tenant\FuecVerificationController;
use App\Http\Controllers\Tenant\FuelController;
use App\Http\Controllers\Tenant\MaintenanceController;
use App\Http\Controllers\Tenant\ManagerialReportController;
use App\Http\Controllers\Tenant\PartnerController;
use App\Http\Controllers\Tenant\PortalController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\RoleController;
use App\Http\Controllers\Tenant\RouteController;
use App\Http\Controllers\Tenant\ServiceOrderController;
use App\Http\Controllers\Tenant\TenantSwitchController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\VehicleController;
use App\Http\Controllers\Tenant\WallboardController;
use App\Http\Middleware\InitializeTenancyForAppPanel;
use Illuminate\Support\Facades\Route;

// Redirección de la raíz hacia el admin (en dominio central) o /app (en subdominio tenant)
Route::get('/', function () {
    $host = request()->getHost();
    $centralDomains = config('tenancy.central_domains', []);
    if (in_array($host, $centralDomains)) {
        return redirect('/admin');
    }

    return redirect('/app');
});

Route::get('/app/login', [LoginController::class, 'showLoginForm']);

// Rutas de Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Redirección transparente de /app a /dashboard
Route::get('/app', function () {
    return redirect()->route('dashboard');
});

// Portales Especializados (Conductor, Cliente, Aliado) y Verificación Pública FUEC
Route::middleware(['web'])->group(function () {
    Route::get('/portal', [PortalController::class, 'conductor'])->name('portal.home');
    Route::get('/portal/conductor', [PortalController::class, 'conductor'])->name('portal.conductor');
    Route::post('/portal/conductor/iniciar/{id}', [PortalController::class, 'iniciarViaje'])->name('portal.conductor.iniciar');
    Route::post('/portal/conductor/finalizar/{id}', [PortalController::class, 'finalizarViaje'])->name('portal.conductor.finalizar');
    Route::post('/portal/conductor/checklist', [PortalController::class, 'guardarChecklist'])->name('portal.conductor.checklist');
    Route::post('/portal/conductor/combustible', [PortalController::class, 'guardarCombustible'])->name('portal.conductor.combustible');
    Route::post('/portal/conductor/novedad', [PortalController::class, 'reportarNovedad'])->name('portal.conductor.novedad');

    Route::get('/portal/cliente', [PortalController::class, 'cliente'])->name('portal.cliente');
    Route::get('/portal/aliado', [PortalController::class, 'aliado'])->name('portal.aliado');

    Route::get('/fuec/pdf/{id}', [FuecVerificationController::class, 'downloadById'])->name('tenant.fuec.pdf');
    Route::get('/fuec/verify/{fuec_number}', [FuecVerificationController::class, 'verify'])->name('tenant.fuec.verify');
    Route::get('/fuec/download/{fuec_number}', [FuecVerificationController::class, 'download'])->name('tenant.fuec.download');
});

// Rutas Operativas Protegidas (Arquitectura Monolítica Modular estilo sys-POS)
Route::middleware(['web', 'auth', InitializeTenancyForAppPanel::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Selector de Tenant / Empresa
    Route::post('/tenants/switch', [TenantSwitchController::class, 'switch'])->name('tenants.switch');

    // Flota de Vehículos
    Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::get('/crear', [VehicleController::class, 'create'])->name('create');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::get('/{vehiculo}', [VehicleController::class, 'show'])->name('show');
        Route::get('/{vehiculo}/editar', [VehicleController::class, 'edit'])->name('edit');
        Route::put('/{vehiculo}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehiculo}', [VehicleController::class, 'destroy'])->name('destroy');
        Route::post('/{vehiculo}/toggle-status', [VehicleController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Órdenes de Servicio
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
        Route::get('/', [ServiceOrderController::class, 'index'])->name('index');
        Route::get('/crear', [ServiceOrderController::class, 'create'])->name('create');
        Route::post('/', [ServiceOrderController::class, 'store'])->name('store');
        Route::get('/{ordene}', [ServiceOrderController::class, 'show'])->name('show');
        Route::get('/{ordene}/editar', [ServiceOrderController::class, 'edit'])->name('edit');
        Route::put('/{ordene}', [ServiceOrderController::class, 'update'])->name('update');
        Route::delete('/{ordene}', [ServiceOrderController::class, 'destroy'])->name('destroy');
        Route::post('/{ordene}/emitir-fuec', [ServiceOrderController::class, 'emitirFuec'])->name('emitir-fuec');
        Route::post('/{ordene}/status', [ServiceOrderController::class, 'updateStatus'])->name('status');
        Route::post('/{ordene}/duplicar', [ServiceOrderController::class, 'duplicate'])->name('duplicar');
    });

    // FUEC Digital
    Route::get('/fuec', [FuecController::class, 'index'])->name('fuec.index');

    // Conductores & Personal Operativo
    Route::prefix('conductores')->name('conductores.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/crear', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{conductore}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{conductore}/editar', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{conductore}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{conductore}', [EmployeeController::class, 'destroy'])->name('destroy');
    });

    // Mantenimientos
    Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::put('/{mantenimiento}', [MaintenanceController::class, 'update'])->name('update');
        Route::delete('/{mantenimiento}', [MaintenanceController::class, 'destroy'])->name('destroy');
    });

    // Combustible
    Route::prefix('combustible')->name('combustible.')->group(function () {
        Route::get('/', [FuelController::class, 'index'])->name('index');
        Route::post('/', [FuelController::class, 'store'])->name('store');
        Route::put('/{combustible}', [FuelController::class, 'update'])->name('update');
        Route::delete('/{combustible}', [FuelController::class, 'destroy'])->name('destroy');
    });

    // Wallboard GPS en Vivo
    Route::get('/monitoreo', WallboardController::class)->name('monitoreo.index');

    // Clientes Corporativos y Particulares
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/crear', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/{cliente}', [ClientController::class, 'show'])->name('show');
        Route::get('/{cliente}/editar', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{cliente}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{cliente}', [ClientController::class, 'destroy'])->name('destroy');
    });

    // Contratos de Prestación de Servicio
    Route::prefix('contratos')->name('contratos.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/crear', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/{contrato}', [ContractController::class, 'show'])->name('show');
        Route::get('/{contrato}/editar', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{contrato}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{contrato}', [ContractController::class, 'destroy'])->name('destroy');
    });

    // Catálogo Maestro de Rutas Frecuentes
    Route::prefix('rutas')->name('rutas.')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('index');
        Route::post('/', [RouteController::class, 'store'])->name('store');
        Route::get('/buscar', [RouteController::class, 'search'])->name('search');
        Route::put('/{ruta}', [RouteController::class, 'update'])->name('update');
        Route::delete('/{ruta}', [RouteController::class, 'destroy'])->name('destroy');
    });

    // Empresas Aliadas y Convenios
    Route::prefix('aliados')->name('aliados.')->group(function () {
        Route::get('/', [PartnerController::class, 'index'])->name('index');
        Route::post('/', [PartnerController::class, 'store'])->name('store');
        Route::get('/{aliado}', [PartnerController::class, 'show'])->name('show');
        Route::put('/{aliado}', [PartnerController::class, 'update'])->name('update');
        Route::delete('/{aliado}', [PartnerController::class, 'destroy'])->name('destroy');
    });

    // Auditoría de Inspección Preoperacional
    Route::prefix('checklists')->name('checklists.')->group(function () {
        Route::get('/', [ChecklistController::class, 'index'])->name('index');
        Route::get('/{checklist}', [ChecklistController::class, 'show'])->name('show');
        Route::get('/{checklist}/pdf', [ChecklistController::class, 'pdf'])->name('pdf');
    });

    // Auditoría y Control Gerencial (Paridad V1 reportes_gerenciales)
    Route::prefix('gerencial')->name('gerencial.')->group(function () {
        // Aprobaciones Gerenciales
        Route::prefix('aprobaciones')->name('aprobaciones.')->group(function () {
            Route::get('/', [ApprovalRequestController::class, 'index'])->name('index');
            Route::post('/', [ApprovalRequestController::class, 'store'])->name('store');
            Route::post('/{approval}/aprobar', [ApprovalRequestController::class, 'approve'])->name('approve');
            Route::post('/{approval}/rechazar', [ApprovalRequestController::class, 'reject'])->name('reject');
        });

        // Reportes Gerenciales, KPIs y Gráficos (V1 reportes_gerenciales/index.php + area_chart.php)
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', [ManagerialReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/chart-semanal', [ManagerialReportController::class, 'chartSemanal'])->name('chart-semanal');
            Route::get('/chart-combustible', [ManagerialReportController::class, 'chartCombustible'])->name('chart-combustible');
            Route::get('/chart-pagos', [ManagerialReportController::class, 'chartPagos'])->name('chart-pagos');
            Route::get('/chart-facturas', [ManagerialReportController::class, 'chartFacturas'])->name('chart-facturas');
            Route::get('/chart-estados', [ManagerialReportController::class, 'chartEstados'])->name('chart-estados');
            Route::get('/chart-rutas', [ManagerialReportController::class, 'chartRutas'])->name('chart-rutas');
            Route::get('/eventos-calendario', [ManagerialReportController::class, 'eventosCalendario'])->name('chart-calendario');

            // Subreportes de Cartera y Facturación (V1 reportes_gerenciales/ordenes/*)
            Route::get('/ordenes-pagadas', [ManagerialReportController::class, 'ordenesPagadas'])->name('ordenes-pagadas');
            Route::get('/ordenes-sin-pagar', [ManagerialReportController::class, 'ordenesSinPagar'])->name('ordenes-sin-pagar');
            Route::get('/ordenes-facturadas', [ManagerialReportController::class, 'ordenesFacturadas'])->name('ordenes-facturadas');
            Route::get('/ordenes-no-facturadas', [ManagerialReportController::class, 'ordenesNoFacturadas'])->name('ordenes-no-facturadas');
        });
    });

    // Reportes Gerenciales y Liquidación de Servicios
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/{ordene}/liquidar', [ReportController::class, 'liquidar'])->name('liquidar');
    });

    // Usuarios del Sistema
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/crear', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{usuario}', [UserController::class, 'show'])->name('show');
        Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [UserController::class, 'update'])->name('update');
        Route::delete('/{usuario}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{usuario}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Roles y Permisos
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/editar', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});
