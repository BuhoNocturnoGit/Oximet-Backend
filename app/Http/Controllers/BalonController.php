<?php

namespace App\Http\Controllers;

use App\Models\Balon;
use Illuminate\Http\Request;

class BalonController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'serie_balon' => ['required', 'string', 'max:50', 'unique:balones,serie_balon'],
            'codigo_barras' => ['nullable', 'string', 'max:50'],
            'id_tipo' => ['required', 'integer', 'exists:tipo_balon,id_tipo'],
            'id_estado' => ['required', 'integer', 'exists:estado_balon,id_estado'],
            'origen' => ['required', 'string', 'in:HRC,Praxair,Custodia'],
            'propiedad' => ['nullable', 'string', 'in:Propio,Alquilado,Comodato,Donacion'],
            'id_proveedor' => ['nullable', 'string', 'max:11', 'exists:proveedors,id_proveedor'],
            'capacidad_m3' => ['nullable', 'numeric'],
            'fecha_fabricacion' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after:fecha_fabricacion'],
            'fecha_prueba_hidrostatica' => ['nullable', 'date'],
            'fecha_cambio_valvula' => ['nullable', 'date'],
            'numero_lote_praxair' => ['nullable', 'string', 'max:50'],
            'guia_remision_praxair' => ['nullable', 'string', 'max:50'],
            'max_cargas' => ['nullable', 'integer', 'min:0'],
            'cargas_utilizadas' => ['nullable', 'integer', 'min:0'],
            'estado_operativo' => ['nullable', 'string', 'in:Operativo,Inoperativo,Mantenimiento,Descartado,Perdido'],
            'condicion' => ['nullable', 'string', 'in:Nuevo,Usado,Reparado,Reciclado'],
            'observaciones' => ['nullable', 'string'],
            'presion_actual_psi' => ['nullable', 'numeric', 'between:0,3000'],
            'o2_disponible_m3' => ['nullable', 'numeric'],
            'pureza_actual' => ['nullable', 'numeric'],
            'numero_recargas_total' => ['nullable', 'integer', 'min:0'],
            'fecha_ultima_recarga' => ['nullable', 'date'],
            'fecha_ultimo_mantenimiento' => ['nullable', 'date'],
            'id_ubicacion_actual' => ['nullable', 'integer', 'exists:ubicacion,id_ubicacion'],
        ]);

        $validated['max_cargas'] = $validated['max_cargas'] ?? 3;
        $validated['cargas_utilizadas'] = $validated['cargas_utilizadas'] ?? 0;
        $validated['cargas_disponibles'] = $validated['max_cargas'] - $validated['cargas_utilizadas'];
        $validated['id_usuario_registro'] = $request->user()->ID_Personal;

        $balon = Balon::create($validated);

        return response()->json($balon->fresh(), 201);
    }
}
