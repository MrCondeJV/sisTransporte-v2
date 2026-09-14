<?php

use App\Http\Controllers\Tenant\FuecVerificationController;
use App\Http\Controllers\Tenant\PortalController;
use Illuminate\Support\Facades\Route;

// Portales Especializados (Conductor, Cliente, Aliado) con Frontend moderno tipo sys-POS
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
    Route::get('/fuec/verify/{fuec_number}', [FuecVerificationController::class, 'verify']);
    Route::get('/fuec/download/{fuec_number}', [FuecVerificationController::class, 'download']);
});

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return redirect('/admin');
        });
    });
}
