<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Usuario;

class Venta extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'ventas';

    // La tabla ventas no tiene created_at ni updated_at
    public $timestamps = false;

    // Campos que se pueden guardar mediante create()
    protected $fillable = [
        'id_vendedor',
        'id_cliente',
        'fecha',
        'total',
        'pago',
    ];

    // Relación: una venta pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id'
        );
    }

    // Relación: una venta pertenece a un vendedor
    public function vendedor()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_vendedor',
            'id'
        );
    }

    // Relación: una venta tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(
            DetalleVenta::class,
            'id_venta',
            'id'
        );
    }
}