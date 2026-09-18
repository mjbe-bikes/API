<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Venta;
use App\Models\Producto;

class DetalleVenta extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'detalles_venta';

    // La tabla no tiene created_at ni updated_at
    public $timestamps = false;

    // Campos que se pueden guardar mediante create() o update()
    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario_momento',
    ];

    // Relación: este detalle pertenece a una venta
    public function venta()
    {
        return $this->belongsTo(
            Venta::class,
            'id_venta',
            'id'
        );
    }

    // Relación: este detalle pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'id_producto',
            'id'
        );
    }
}