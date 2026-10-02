<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    public $timestamps = false;
    // Nombre de la tabla en la base de datos
    protected $table = 'proveedores';

    // Campos que Laravel permite guardar o actualizar
    protected $fillable = [
        'nombre_proveedor',
        'tipo_documento_id',
        'numero_identidad',
        'direccion',
        'telefono_prv',
        'estado',
    ];
}

