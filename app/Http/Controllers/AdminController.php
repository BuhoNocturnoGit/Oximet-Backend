<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function listarPendientes()
    {
        $pendientes = Personal::where('estado', 'Bloqueado')->get();

        return response()->json($pendientes, 200);
    }

    public function aprobarUsuario(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|integer|in:1,2,3',
            'comentarios_aprobacion' => 'nullable|string',
        ]);

        $usuario = Personal::find($id);

        if (! $usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->estado = 'Activo';
        $usuario->id_rol = $request->rol;
        $usuario->ID_Admin_Aprobador = $request->user()->ID_Personal;
        $usuario->Fecha_Aprobacion = now();
        $usuario->Comentarios_Aprobacion = $request->comentarios_aprobacion;
        $usuario->ID_Usuario_Modificacion = $request->user()->ID_Personal;
        $usuario->Fecha_Modificacion = now();
        $usuario->save();

        return response()->json([
            'message' => 'Usuario aprobado exitosamente',
            'usuario' => $usuario,
        ], 200);
    }
}
