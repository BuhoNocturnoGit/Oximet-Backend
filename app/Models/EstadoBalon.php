<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoBalon extends Model
{
    protected $table = 'estado_balon';

    protected $primaryKey = 'id_estado';

    public $timestamps = false;

    protected $fillable = [
        'nombre_estado',
    ];

    public static function idDe(string $nombre): int
    {
        return (int) static::query()->where('nombre_estado', $nombre)->value('id_estado');
    }
}
