<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre'];

    public function personal()
    {
        return $this->hasMany(Personal::class, 'id_rol', 'id');
    }
}
