<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    // La tabla locales no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'locales';

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'nombre_local',
        'direccion_local',
        'correo',
        'telefono',
        'estado',
    ];
}
