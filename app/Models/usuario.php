<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Model implements JWTSubject, Authenticatable
{
    // La tabla usuarios no utiliza created_at ni updated_at
    public $timestamps = false;

    // Nombre de la tabla en la base de datos
    protected $table = 'usuarios';

    // Campos que Laravel permite guardar mediante Usuario::create()
    protected $fillable = [
        'login',
        'email',
        'password_harsh',
        'rol_id',
        'estado',
        'token_activacion',
        'reset_key',
        'reset_base',
    ];

    // La contraseña no se muestra en las respuestas JSON por defecto
    protected $hidden = [
        'password_harsh',
    ];

    // =========================================================
    // AUTENTICACIÓN DE LARAVEL
    // =========================================================

    // Indica qué columna identifica al usuario.
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    // Devuelve el ID del usuario autenticado.
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    // Indica qué campo contiene la contraseña.
    public function getAuthPasswordName()
    {
        return 'password_harsh';
    }

    // Devuelve el valor de la contraseña almacenada.
    public function getAuthPassword()
    {
        return $this->password_harsh;
    }

    // No utilizamos "remember me" en este sistema.
    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value)
    {
        // No hacemos nada porque no utilizamos remember tokens.
    }

    public function getRememberTokenName()
    {
        return '';
    }

    // =========================================================
    // JWT
    // =========================================================

    // Identificador único que JWT utilizará para identificar al usuario.
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Información adicional que se incluirá dentro del JWT.
    public function getJWTCustomClaims()
    {
        return [
            'login' => $this->login,
            'email' => $this->email,
            'rol_id' => $this->rol_id,
        ];
    }
}