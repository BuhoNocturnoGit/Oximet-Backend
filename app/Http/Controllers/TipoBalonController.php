<?php

namespace App\Http\Controllers;

use App\Models\TipoBalon;
use Illuminate\Http\Request;

class TipoBalonController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'capacidad_o2_m3' => 'required|string|max:10',
            'material' => 'required|string|max:20',
            'modelo_valvula' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'norma' => 'required|string|max:50',
            'capacidad_real_m3' => 'required|numeric',
            'volumen_de_tanque' => 'required|numeric',
            'peso_kg' => 'nullable|numeric',
            'altura_cm' => 'nullable|integer',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen_ruta'] = $request->file('imagen')->store('tipos-balon', 'public');
        }

        $tipoBalon = TipoBalon::create($validated);

        return response()->json($tipoBalon, 201);
    }
}
