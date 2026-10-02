<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    // La tabla tipos_documentos no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'tipos_documentos';

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'sigla',
        'nombre_documento',
    ];
}
