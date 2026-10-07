<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * =========================================================
     * REGISTRO DE CLIENTE
     * =========================================================
     *
     * POST /api/register
     *
     * Ruta pública (sin sesión) utilizada por Register.jsx para
     * que un cliente cree su propia cuenta.
     *
     * Por seguridad, el rol y el estado NO llegan desde React:
     * siempre se crea como rol_id = 4 (Cliente) y estado "activo".
     */
    public function register(Request $request)
    {
        // Validamos los datos y que login/email no estén repetidos.
        $datos = $request->validate([
            'login' => 'required|string|max:255|unique:usuarios,login',
            'email' => 'required|email|max:255|unique:usuarios,email',
            'password_harsh' => 'required|string',
        ]);

        // Se generan igual que en UsuarioController::store().
        $usuario = Usuario::create([
            'login' => $datos['login'],
            'email' => $datos['email'],
            'password_harsh' => $datos['password_harsh'],
            'rol_id' => 4,
            'estado' => 'activo',
            'token_activacion' => Str::random(60),
            'reset_key' => Str::random(60),
            'reset_base' => Str::random(60),
        ]);

        return response()->json([
            'mensaje' => 'Registro exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'login' => $usuario->login,
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id,
            ]
        ], 201);
    }

    /**
     * =========================================================
     * LOGIN
     * =========================================================
     *
     * POST /api/login
     *
     * React enviará:
     * {
     *     "email": "...",
     *     "password_harsh": "..."
     * }
     *
     * El JWT se genera aquí y NO se devuelve en el JSON.
     * Se guarda directamente en una cookie HttpOnly.
     */
    public function login(Request $request)
    {
        // Validamos los datos que llegan desde React.
        $datos = $request->validate([
            'email' => 'required|email',
            'password_harsh' => 'required|string',
        ]);

        // Buscamos el usuario por su correo.
        $usuario = Usuario::where(
            'email',
            $datos['email']
        )->first();

        // Si no existe, rechazamos el login.
        if (!$usuario) {
            return response()->json([
                'mensaje' => 'Correo o contraseña incorrectos'
            ], 401);
        }

        // Comparamos directamente el SHA-256 que envía React
        // con el valor almacenado en password_harsh.
        if ($usuario->password_harsh !== $datos['password_harsh']) {
            return response()->json([
                'mensaje' => 'Correo o contraseña incorrectos'
            ], 401);
        }

        // Comprobamos que el usuario esté activo sin importar
// si la base de datos lo guarda como "activo" o "Activo".
if (strtolower($usuario->estado) !== 'activo') {
    return response()->json([
        'mensaje' => 'El usuario no está activo'
    ], 403);
}

        // Generamos el JWT usando el usuario autenticado.
        $token = JWTAuth::fromUser($usuario);

        // Guardamos el JWT en una cookie HttpOnly.
        //
        // React NO podrá leer esta cookie mediante JavaScript.
        // El navegador se encargará de enviarla automáticamente
        // en las siguientes peticiones a Laravel.
        $cookie = cookie(
            'mjbe_token', // Nombre de la cookie
            $token,       // JWT
            config('jwt.ttl'), // Duración del JWT en minutos
            '/',          // Disponible para toda la API
            null,         // Dominio actual
            false,        // Secure: false en desarrollo HTTP
            true,         // HttpOnly: JavaScript no puede leerla
            false,        // Raw
            'lax'         // SameSite
        );

        // Devolvemos solamente información pública del usuario.
        // El JWT NO aparece en el JSON.
        return response()->json([
    'mensaje' => 'Inicio de sesión correcto',

    // Datos públicos del usuario.
    'usuario' => [
        'id' => $usuario->id,
        'login' => $usuario->login,
        'email' => $usuario->email,
        'rol_id' => $usuario->rol_id,
    ],

    // Enviamos también el JWT para que React
    // pueda utilizar Authorization: Bearer <token>.
    'token' => $token,

], 200)->withCookie($cookie);
    }

    /**
     * =========================================================
     * USUARIO AUTENTICADO
     * =========================================================
     *
     * GET /api/me
     *
     * Esta ruta servirá para comprobar que la cookie JWT
     * realmente está autenticando al usuario.
     */
    public function me()
    {
        // auth('api') utiliza el guard JWT configurado en auth.php.
        $usuario = auth('api')->user();

        return response()->json([
            'usuario' => $usuario
        ], 200);
    }

    /**
     * =========================================================
     * LOGOUT
     * =========================================================
     *
     * POST /api/logout
     *
     * Invalida el JWT y elimina la cookie del navegador.
     */
    public function logout()
    {
        // Invalida el JWT actual.
        auth('api')->logout();

        // Eliminamos la cookie que contenía el JWT.
        $cookie = Cookie::forget('mjbe_token');

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente'
        ], 200)->withCookie($cookie);
    }
}