<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    // Tabla correspondiente en la base de datos
    protected $table = 'compras';

    // La tabla no tiene created_at ni updated_at
    public $timestamps = false;

    // Campos que se pueden guardar mediante Compra::create()
    protected $fillable = [
        'id_proveedor',
        'fecha',
        'total_compra',
        'pago',
    ];

    // Conversión automática de tipos
    protected $casts = [
        'fecha' => 'datetime',
        'total_compra' => 'decimal:2',
        'pago' => 'boolean',
    ];

    // Una compra pertenece a un proveedor
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    // Una compra puede tener varios detalles
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }
}