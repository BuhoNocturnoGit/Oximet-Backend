<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required',
            'contrasena' => 'required',
        ]);

        $usuario = Personal::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if ($usuario->estado_registro === 'Pendiente' || $usuario->estado_registro === 'Bloqueado') {
            return response()->json([
                'message' => 'Acceso denegado: Cuenta en revisión o bloqueada'
            ], 403);
        }

        if ($usuario->activo == false) {
            return response()->json([
                'message' => 'Acceso denegado: Cuenta inactiva'
            ], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }
}
