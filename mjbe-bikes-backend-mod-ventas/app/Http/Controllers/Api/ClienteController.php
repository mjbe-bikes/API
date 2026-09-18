<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * =========================================================
     * LISTAR CLIENTES
     * =========================================================
     *
     * GET /api/clientes
     *
     * También permite:
     *
     * GET /api/clientes?usuario_id=5
     *
     * En ese caso devuelve únicamente el cliente relacionado
     * con ese usuario.
     */
    public function index(Request $request)
    {
        try {

            /*
             * Si llega usuario_id desde el frontend,
             * buscamos el cliente relacionado.
             */
            if ($request->filled('usuario_id')) {

                $cliente = Cliente::where(
                    'usuario_id',
                    $request->query('usuario_id')
                )->get();

                return response()->json($cliente, 200);
            }

            /*
             * Si no llega usuario_id, devolvemos todos
             * los clientes.
             */
            $clientes = Cliente::all();

            return response()->json($clientes, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar los clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * MOSTRAR UN CLIENTE
     * =========================================================
     *
     * GET /api/clientes/{id}
     */
    public function show($id)
    {
        try {

            // Buscar cliente por su ID
            $cliente = Cliente::find($id);

            // Verificar si existe
            if (!$cliente) {

                return response()->json([
                    'mensaje' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json($cliente, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * CREAR CLIENTE
     * =========================================================
     *
     * POST /api/clientes
     */
    public function store(Request $request)
    {
        try {

            /*
             * Validar los datos recibidos desde React.
             *
             * usuario_id es opcional porque un cliente puede
             * ser registrado desde el módulo de ventas sin
             * tener todavía una cuenta de usuario.
             *
             * Si se envía un usuario_id, debe existir en
             * la tabla usuarios.
             */
            $datos = $request->validate([

                'usuario_id' =>
                    'nullable|integer|exists:usuarios,id',

                'tipo_documento_id' =>
                    'required|integer|exists:tipos_documentos,id',

                'numero_documento' =>
                    'required|string|max:30',

                'nombres' =>
                    'required|string|max:100',

                'apellidos' =>
                    'required|string|max:100',

                'direccion' =>
                    'required|string|max:150',

                'telefono_clnt' =>
                    'required|string|max:20',
            ]);

            /*
             * Crear el cliente.
             */
            $cliente = Cliente::create($datos);

            /*
             * Devolver el cliente creado.
             */
            return response()->json([
                'mensaje' => 'Cliente creado correctamente',
                'cliente' => $cliente
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            /*
             * Devolver los errores específicos de validación.
             */
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al crear el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * ACTUALIZAR CLIENTE
     * =========================================================
     *
     * PUT /api/clientes/{id}
     */
    public function update(Request $request, $id)
    {
        try {

            // Buscar cliente
            $cliente = Cliente::find($id);

            if (!$cliente) {

                return response()->json([
                    'mensaje' => 'Cliente no encontrado'
                ], 404);
            }

            /*
             * Validar los datos.
             *
             * usuario_id también es opcional para permitir
             * actualizar clientes que no tengan usuario asociado.
             */
            $datos = $request->validate([

                'usuario_id' =>
                    'nullable|integer|exists:usuarios,id',

                'tipo_documento_id' =>
                    'required|integer|exists:tipos_documentos,id',

                'numero_documento' =>
                    'required|string|max:30',

                'nombres' =>
                    'required|string|max:100',

                'apellidos' =>
                    'required|string|max:100',

                'direccion' =>
                    'required|string|max:150',

                'telefono_clnt' =>
                    'required|string|max:20',
            ]);

            /*
             * Actualizar cliente.
             */
            $cliente->update($datos);

            return response()->json([
                'mensaje' => 'Cliente actualizado correctamente',
                'cliente' => $cliente
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al actualizar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * ELIMINAR CLIENTE
     * =========================================================
     *
     * DELETE /api/clientes/{id}
     */
    public function destroy($id)
    {
        try {

            // Buscar cliente
            $cliente = Cliente::find($id);

            if (!$cliente) {

                return response()->json([
                    'mensaje' => 'Cliente no encontrado'
                ], 404);
            }

            /*
             * Intentar eliminar.
             *
             * Si tiene ventas asociadas, MySQL debería impedir
             * la eliminación debido a la FK:
             *
             * ventas.id_cliente -> clientes.id
             */
            $cliente->delete();

            return response()->json([
                'mensaje' => 'Cliente eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al eliminar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}