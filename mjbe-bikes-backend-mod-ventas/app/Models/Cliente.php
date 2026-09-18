<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    // La tabla clientes no tiene created_at ni updated_at
    public $timestamps = false;
    
    // Nombre de la tabla en la base de datos
    protected $table = 'clientes';

    // La tabla utiliza "id" como clave primaria.
    // No es necesario declarar $primaryKey porque Laravel
    // utiliza "id" por defecto.

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'usuario_id',
        'tipo_documento_id',
        'numero_documento',
        'nombres',
        'apellidos',
        'direccion',
        'telefono_clnt',
    ];
}

