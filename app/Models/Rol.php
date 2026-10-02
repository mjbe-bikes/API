<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    // La tabla roles no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'roles';

    // La tabla utiliza id_rol como clave primaria, no id.
    protected $primaryKey = 'id_rol';

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'tipo_rol',
    ];
}
