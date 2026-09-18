<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    /**
     * =========================================================
     * LISTAR USUARIOS
     * =========================================================
     *
     * GET /api/usuarios
     *
     * GET /api/usuarios?email=correo@ejemplo.com
     *
     * La consulta con email se utiliza para el Login y permite
     * mostrar temporalmente password_harsh para comparar el SHA-256.
     */
    public function index(Request $request)
    {
        try {

            // Consulta utilizada por el Login
            if ($request->has('email')) {

                $usuario = Usuario::where(
                    'email',
                    $request->query('email')
                )->first();

                // Si no existe el usuario
                if (!$usuario) {
                    return response()->json([], 200);
                }

                // Mostrar password_harsh solamente para el Login
                $usuario->makeVisible('password_harsh');

                return response()->json([
                    $usuario
                ], 200);
            }

            // Listado normal de todos los usuarios
            $usuarios = Usuario::all();

            return response()->json($usuarios, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar los usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * MOSTRAR UN USUARIO
     * =========================================================
     *
     * GET /api/usuarios/{id}
     */
    public function show($id)
    {
        try {

            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json($usuario, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * CREAR USUARIO
     * =========================================================
     *
     * POST /api/usuarios
     *
     * Se generan automáticamente los campos:
     * - token_activacion
     * - reset_key
     * - reset_base
     */
    public function store(Request $request)
    {
        try {

            // Validamos únicamente los campos que puede enviar
            // el frontend al crear el usuario.
            $datos = $request->validate([
                'login' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password_harsh' => 'required|string',
                'rol_id' => 'required|integer',
                'estado' => 'required|string',

                // Este campo puede llegar vacío.
                'reset_base' => 'nullable|string|max:255',
            ]);

            // Genera automáticamente el token de activación.
            $datos['token_activacion'] = Str::random(60);

            // Genera automáticamente la clave para recuperación.
            $datos['reset_key'] = Str::random(60);

            // Genera automáticamente reset_base.
            $datos['reset_base'] = Str::random(60);

            // Crea el usuario en la base de datos.
            $usuario = Usuario::create($datos);

            return response()->json([
                'mensaje' => 'Usuario creado correctamente',
                'usuario' => $usuario
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al crear el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * ACTUALIZAR USUARIO
     * =========================================================
     *
     * PUT /api/usuarios/{id}
     */
    public function update(Request $request, $id)
    {
        try {

            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Usuario no encontrado'
                ], 404);
            }

            // Validamos los datos que pueden modificarse.
            $datos = $request->validate([
                'login' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'rol_id' => 'required|integer',
                'estado' => 'required|string',
                'password_harsh' => 'nullable|string'
            ]);

            // Actualizamos los datos principales.
            $usuario->login = $datos['login'];
            $usuario->email = $datos['email'];
            $usuario->rol_id = $datos['rol_id'];
            $usuario->estado = $datos['estado'];

            // Solo cambia la contraseña si se envió una nueva.
            if (!empty($datos['password_harsh'])) {
                $usuario->password_harsh = $datos['password_harsh'];
            }

            // Guardamos los cambios.
            $usuario->save();

            return response()->json([
                'mensaje' => 'Usuario actualizado correctamente',
                'usuario' => $usuario
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * ELIMINAR USUARIO
     * =========================================================
     *
     * DELETE /api/usuarios/{id}
     *
     * Si el usuario tiene registros relacionados en otras tablas,
     * MySQL impedirá eliminarlo debido a las claves foráneas
     * configuradas con ON DELETE RESTRICT.
     */
    public function destroy($id)
    {
        try {

            // Busca el usuario que se quiere eliminar.
            $usuario = Usuario::find($id);

            // Si el usuario no existe, devuelve 404.
            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Usuario no encontrado'
                ], 404);
            }

            // Intenta eliminar el usuario.
            // Si tiene registros relacionados, MySQL impedirá
            // la eliminación para proteger la integridad de los datos.
            $usuario->delete();

            return response()->json([
                'mensaje' => 'Usuario eliminado correctamente'
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {

            // SQLSTATE 23000 corresponde a errores de integridad
            // como las restricciones de claves foráneas.
            if ($e->getCode() === '23000') {

                return response()->json([
                    'mensaje' => 'No se puede eliminar este usuario porque tiene registros relacionados en el sistema.'
                ], 409);
            }

            // Si es otro error de base de datos, mantenemos el 500.
            return response()->json([
                'mensaje' => 'Error de base de datos al eliminar el usuario',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {

            // Cualquier otro error inesperado.
            return response()->json([
                'mensaje' => 'Error al eliminar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
