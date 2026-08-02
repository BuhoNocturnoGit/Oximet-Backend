<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Personal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:100',
            'Apellidos' => 'required|string|max:100',
            'Correo' => 'required|email|unique:personal,Correo',
            'Contrasena' => 'required|string|min:6',
            'id_rol' => 'required|integer|exists:roles,id',
            'Telefono' => 'nullable|string|max:15',
            'Firma_Digital' => 'nullable|string|max:255',
        ]);

        $usuario = Personal::create([
            'Nombre' => $request->Nombre,
            'Apellidos' => $request->Apellidos,
            'Correo' => $request->Correo,
            'Contrasena' => Hash::make($request->Contrasena),
            'id_rol' => $request->id_rol,
            'Telefono' => $request->Telefono,
            'Firma_Digital' => $request->Firma_Digital,
            'estado' => 'Activo',
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = Personal::findOrFail($id);

        $request->validate([
            'Nombre' => 'sometimes|string|max:100',
            'Apellidos' => 'sometimes|string|max:100',
            'Correo' => 'sometimes|email|unique:personal,Correo,'.$id.',ID_Personal',
            'id_rol' => 'sometimes|integer|exists:roles,id',
            'Telefono' => 'nullable|string|max:15',
            'Firma_Digital' => 'nullable|string|max:255',
        ]);

        if ($request->has('Contrasena')) {
            $usuario->Contrasena = Hash::make($request->Contrasena);
        }

        $usuario->fill($request->except('Contrasena'));
        $usuario->save();

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $usuario = Personal::findOrFail($id);
        
        $usuario->estado = $usuario->estado === 'Activo' ? 'Bloqueado' : 'Activo';
        $usuario->save();

        Log::info("El estado del usuario {$usuario->Correo} ha cambiado a {$usuario->estado}.");

        return response()->json([
            'message' => 'Estado actualizado',
            'estado_actual' => $usuario->estado
        ], 200);
    }
}
