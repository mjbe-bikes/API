<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    // La tabla productos no tiene created_at ni updated_at.
    public $timestamps = false;

    // Nombre de la tabla en la base de datos.
    protected $table = 'productos';

    // Campos que Laravel puede guardar o actualizar.
    protected $fillable = [
        'img_producto',
        'nombre_producto',
        'descripcion',
        'color_producto',
        'marca_producto',
        'cant_producto',
        'modelo',
        'id_talla',
        'id_proveedor',
        'id_local',
        'valor_unitario',
        'estado',
    ];
}