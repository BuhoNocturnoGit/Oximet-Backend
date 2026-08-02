<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Storage;

class ProveedorController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación de Datos
        $validated = $request->validate([
            'id_proveedor'        => 'required|string|size:11|unique:proveedor,id_proveedor',
            'nombre'              => 'required|string|max:100',
            'direccion'           => 'required|string|max:200',
            'contacto_telefonico' => 'required|string|max:15',
            'contacto_email'      => 'nullable|email|max:100',
            'contacto_nombre'     => 'nullable|string|max:100',
            'tipo_contrato'       => 'nullable|string|max:50',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Procesamiento de la Imagen
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            // Guarda el archivo en storage/app/public/proveedores
            $rutaImagen = $request->file('imagen')->store('proveedores', 'public');
        }

        // 3. Inserción en Base de Datos (Forzando defaults)
        $proveedor = Proveedor::create([
            'id_proveedor'        => $validated['id_proveedor'],
            'nombre'              => $validated['nombre'],
            'direccion'           => $validated['direccion'],
            'contacto_telefonico' => $validated['contacto_telefonico'],
            'contacto_email'      => $validated['contacto_email'] ?? null,
            'contacto_nombre'     => $validated['contacto_nombre'] ?? null,
            'tipo_contrato'       => $validated['tipo_contrato'] ?? null,
            'activo'              => 1, // Forzado a true
            'fecha_registro'      => now(), // Timestamp actual forzado
            'imagen_ruta'         => $rutaImagen,
        ]);

        // 4. Retornar Respuesta HTTP 201 (Created)
        return response()->json([
            'mensaje' => 'Proveedor registrado exitosamente',
            'data'    => $proveedor
        ], 201);
    }
}