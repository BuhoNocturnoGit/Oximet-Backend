<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'check.admin'])->group(function () {
    Route::post('/usuarios', [\App\Http\Controllers\UsuarioController::class, 'store']);
    Route::put('/usuarios/{id}', [\App\Http\Controllers\UsuarioController::class, 'update']);
    Route::patch('/usuarios/{id}/estado', [\App\Http\Controllers\UsuarioController::class, 'cambiarEstado']);
});
