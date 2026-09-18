<?php

use App\Models\Usuario;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Laravel seguirá teniendo "web" como guard por defecto.
    | Para la API utilizaremos explícitamente el guard "api",
    | configurado más abajo con JWT.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | "web" mantiene la autenticación tradicional de Laravel.
    |
    | "api" será utilizado por nuestra API y funcionará mediante
    | JWT. El JWT posteriormente se guardará en una cookie HttpOnly.
    |
    */

    'guards' => [

        // Guard tradicional de Laravel
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard utilizado por nuestra API
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | En lugar del modelo User que trae Laravel por defecto,
    | nuestra aplicación utiliza el modelo Usuario.
    |
    | Ese modelo representa la tabla "usuarios" de nuestra base de datos
    | y además implementa JWTSubject para poder trabajar con JWT.
    |
    */

    'providers' => [

        'users' => [
            'driver' => 'eloquent',
            'model' => Usuario::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [

        'users' => [
            'provider' => 'users',
            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env(
        'AUTH_PASSWORD_TIMEOUT',
        10800
    ),

];