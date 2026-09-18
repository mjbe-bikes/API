<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    // Tabla correspondiente en la base de datos
    protected $table = 'detalles_compra';

    // La tabla no tiene created_at ni updated_at
    public $timestamps = false;

    // Campos permitidos para crear un detalle
    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario_momento',
    ];

    // Conversión automática de tipos
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario_momento' => 'decimal:2',
    ];

    // El detalle pertenece a una compra
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    // El detalle pertenece a un producto
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}