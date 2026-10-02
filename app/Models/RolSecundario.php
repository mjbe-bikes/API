<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolSecundario extends Model
{
    // La tabla roles_secundarios no tiene created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'roles_secundarios';

    // La tabla no tiene una columna id: la clave es la
    // combinación (usuario_id, rol_secundario). Por eso no
    // usamos find()/save() y deshabilitamos el autoincremento.
    protected $primaryKey = null;
    public $incrementing = false;

    // Campos que se pueden guardar
    protected $fillable = [
        'usuario_id',
        'rol_secundario',
    ];

    // Relación: pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación: pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_secundario', 'id_rol');
    }
}
