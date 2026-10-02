<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talla extends Model
{
    // La tabla tallas no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'tallas';

    // Campos que se pueden guardar o actualizar
    protected $fillable = [
        'medida_id',
        'talla',
    ];

    // Relación: una talla pertenece a una medida
    public function medida()
    {
        return $this->belongsTo(Medida::class, 'medida_id');
    }
}
