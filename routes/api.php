<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/usuarios/pendientes', [\App\Http\Controllers\AdminController::class, 'listarPendientes']);
    Route::put('/admin/usuarios/{id}/aprobar', [\App\Http\Controllers\AdminController::class, 'aprobarUsuario']);
});
