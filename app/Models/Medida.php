<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medida extends Model
{
    // La tabla medidas no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'medidas';

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'tipo_medida',
    ];
}
