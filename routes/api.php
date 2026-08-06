<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AtencionSisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InformePisoController;
use App\Http\Controllers\RenoxiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/usuarios/pendientes', [AdminController::class, 'listarPendientes']);
    Route::put('/admin/usuarios/{id}/aprobar', [AdminController::class, 'aprobarUsuario']);
});

Route::prefix('distribucion')->middleware('auth:sanctum')->group(function () {
    Route::post('/informes/iniciar', [InformePisoController::class, 'iniciarRecorrido']);
    Route::put('/informes/{id}/cerrar', [InformePisoController::class, 'cerrarRecorrido']);
    Route::post('/informes/{id_informe}/intercambio', [InformePisoController::class, 'registrarIntercambio']);
});

Route::prefix('sis')->middleware('auth:sanctum')->group(function () {
    Route::post('/pacientes/buscar-crear', [AtencionSisController::class, 'buscarOCrearPaciente']);
    Route::post('/reportes/abrir', [AtencionSisController::class, 'abrirReporteDiario']);
    Route::get('/balones/{serie_balon}/verificar', [AtencionSisController::class, 'verificarBalonSIS']);
    Route::post('/atenciones/asignar', [AtencionSisController::class, 'asignarBalon']);
    Route::post('/atenciones/devolver', [AtencionSisController::class, 'devolverBalon']);
    Route::put('/reportes/{id_reporte}/cerrar', [AtencionSisController::class, 'cerrarReporteDiario']);
});

Route::prefix('renoxi')->middleware('auth:sanctum')->group(function () {
    Route::post('/balances/abrir', [RenoxiController::class, 'abrirBalanceDiario']);
    Route::get('/balances/{id_reporte}/pre-cierre', [RenoxiController::class, 'previsualizarConsolidado']);
    Route::put('/balances/{id_reporte}/cerrar', [RenoxiController::class, 'cerrarBalanceDiario']);
});

Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/consolidado', [DashboardController::class, 'obtenerConsolidado']);
});
