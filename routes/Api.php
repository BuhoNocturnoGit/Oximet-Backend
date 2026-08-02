<?php

use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

// Agrupa las rutas de administración
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Endpoint para registrar proveedores
    Route::post('/admin/proveedores', [ProveedorController::class, 'store']);
    
});