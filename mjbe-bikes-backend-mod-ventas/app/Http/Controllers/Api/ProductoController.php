<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // =========================================================
    // MOSTRAR TODOS LOS PRODUCTOS
    // =========================================================
    public function index()
    {
        try {
            $productos = Producto::all();

            return response()->json($productos);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al obtener los productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // CREAR UN PRODUCTO
    // =========================================================
    public function store(Request $request)
    {
        try {
            // Normalizar el estado antes de validar.
            // Esto permite recibir "Activo", "ACTIVO" o "activo".
            if ($request->has('estado')) {
                $request->merge([
                    'estado' => strtolower(trim($request->estado))
                ]);
            }

            // Validar los datos recibidos desde React.
            // La tabla productos utiliza id_talla, no id_medida.
            $datos = $request->validate([
                'img_producto' => 'required|string|max:255',
                'nombre_producto' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'color_producto' => 'required|string|max:100',
                'marca_producto' => 'required|string|max:100',
                'cant_producto' => 'required|integer|min:0',
                'modelo' => 'required|string|max:100',
                'id_talla' => 'required|integer|exists:tallas,id',
                'id_proveedor' => 'required|integer|exists:proveedores,id',
                'id_local' => 'required|integer|exists:locales,id',
                'valor_unitario' => 'required|numeric|min:0',
                'estado' => 'required|in:activo,inactivo',
            ]);

            // Crear el producto con los datos validados.
            $producto = Producto::create($datos);

            // Responder con el producto creado.
            return response()->json([
                'mensaje' => 'Producto creado correctamente',
                'producto' => $producto
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            // Devolver exactamente qué campo produjo el error 422.
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            // Error general del servidor.
            return response()->json([
                'mensaje' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // MOSTRAR UN PRODUCTO ESPECÍFICO
    // =========================================================
    public function show($id)
    {
        try {
            $producto = Producto::find($id);

            // Verificar si el producto existe.
            if (!$producto) {
                return response()->json([
                    'mensaje' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json($producto);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al obtener el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // ACTUALIZAR UN PRODUCTO
    // =========================================================
    public function update(Request $request, $id)
    {
        try {
            // Normalizar el estado antes de validar.
            // Ejemplos:
            // "Activo"   -> "activo"
            // "ACTIVO"   -> "activo"
            // "Inactivo" -> "inactivo"
            if ($request->has('estado')) {
                $request->merge([
                    'estado' => strtolower(trim($request->estado))
                ]);
            }

            // Validar los datos recibidos desde React.
            $datos = $request->validate([
                'img_producto' => 'required|string|max:255',
                'nombre_producto' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'color_producto' => 'required|string|max:100',
                'marca_producto' => 'required|string|max:100',
                'cant_producto' => 'required|integer|min:0',
                'modelo' => 'required|string|max:100',
                'id_talla' => 'required|integer|exists:tallas,id',
                'id_proveedor' => 'required|integer|exists:proveedores,id',
                'id_local' => 'required|integer|exists:locales,id',
                'valor_unitario' => 'required|numeric|min:0',
                'estado' => 'required|in:activo,inactivo',
            ]);

            // Buscar el producto por su ID.
            $producto = Producto::find($id);

            // Verificar si el producto existe.
            if (!$producto) {
                return response()->json([
                    'mensaje' => 'Producto no encontrado'
                ], 404);
            }

            // Actualizar únicamente los campos validados.
            $producto->update($datos);

            // Recargar el producto para devolver los datos actualizados.
            $producto->refresh();

            return response()->json([
                'mensaje' => 'Producto actualizado correctamente',
                'producto' => $producto
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            // Devolver exactamente qué campo produjo el error 422.
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            // Error general del servidor.
            return response()->json([
                'mensaje' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // ELIMINAR UN PRODUCTO
    // =========================================================
    public function destroy($id)
    {
        try {
            $producto = Producto::find($id);

            // Verificar si el producto existe.
            if (!$producto) {
                return response()->json([
                    'mensaje' => 'Producto no encontrado'
                ], 404);
            }

            // Eliminar el producto.
            $producto->delete();

            return response()->json([
                'mensaje' => 'Producto eliminado correctamente'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
