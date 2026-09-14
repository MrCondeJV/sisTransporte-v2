<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Api\DriverAuthController;
use App\Http\Controllers\Tenant\Api\DriverChecklistController;
use App\Http\Controllers\Tenant\Api\DriverFuelController;
use App\Http\Controllers\Tenant\Api\DriverIncidentController;
use App\Http\Controllers\Tenant\Api\DriverOrderController;
use App\Http\Controllers\Tenant\Api\DriverTelemetryController;
use App\Http\Controllers\Tenant\FuecVerificationController;
use App\Http\Middleware\IdentifyTenantForApi;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return redirect('/app');
    });

    Route::get('/fuec/verify/{fuec_number}', [FuecVerificationController::class, 'verify'])
        ->name('tenant.fuec.verify');

    Route::get('/fuec/download/{fuec_number}', [FuecVerificationController::class, 'download'])
        ->name('tenant.fuec.download');
});

Route::prefix('api/v1')->middleware([
    'api',
    IdentifyTenantForApi::class,
])->group(function () {
    Route::post('driver/login', [DriverAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('driver/profile', [DriverAuthController::class, 'profile']);
        Route::post('driver/logout', [DriverAuthController::class, 'logout']);

        Route::get('driver/orders', [DriverOrderController::class, 'index']);
        Route::get('driver/orders/{id}', [DriverOrderController::class, 'show']);
        Route::post('driver/orders/{id}/status', [DriverOrderController::class, 'updateStatus']);

        Route::post('driver/checklist', [DriverChecklistController::class, 'store']);
        Route::post('driver/incidents', [DriverIncidentController::class, 'store']);
        Route::post('driver/fuel', [DriverFuelController::class, 'store']);
        Route::post('driver/telemetry/ping', [DriverTelemetryController::class, 'ping']);
        Route::post('driver/telemetry/batch', [DriverTelemetryController::class, 'batch']);
    });
});
