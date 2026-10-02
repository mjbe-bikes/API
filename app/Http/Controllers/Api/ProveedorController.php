<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    // Mostrar todos los proveedores
    public function index()
    {
        try {
            // Obtener todos los proveedores
            $proveedores = Proveedor::all();

            return response()->json($proveedores, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un proveedor por ID
    public function show($id)
    {
        try {
            // Buscar proveedor por ID
            $proveedor = Proveedor::find($id);

            // Verificar si existe
            if (!$proveedor) {
                return response()->json([
                    'mensaje' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json($proveedor, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo proveedor
    public function store(Request $request)
    {
        try {
            // Validar los datos recibidos
            $datos = $request->validate([
                'nombre_proveedor' => 'required|string|max:100',

                // El tipo de documento debe existir en tipos_documentos
                'tipo_documento_id' => 'required|integer|exists:tipos_documentos,id',

                'numero_identidad' => 'required|string|max:30',
                'direccion' => 'required|string|max:150',

                // IMPORTANTE: el campo real de la BD es telefono_prv
                'telefono_prv' => 'required|string|max:20',

                'estado' => 'required|in:activo,inactivo',
            ]);

            // Crear el proveedor
            $proveedor = Proveedor::create($datos);

            return response()->json([
                'mensaje' => 'Proveedor creado correctamente',
                'proveedor' => $proveedor
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar proveedor
    public function update(Request $request, $id)
    {
        try {
            // Buscar proveedor
            $proveedor = Proveedor::find($id);

            // Verificar si existe
            if (!$proveedor) {
                return response()->json([
                    'mensaje' => 'Proveedor no encontrado'
                ], 404);
            }

            // Validar datos recibidos
            $datos = $request->validate([
                'nombre_proveedor' => 'required|string|max:100',

                // Verificar que el tipo de documento exista
                'tipo_documento_id' => 'required|integer|exists:tipos_documentos,id',

                'numero_identidad' => 'required|string|max:30',
                'direccion' => 'required|string|max:150',

                // IMPORTANTE: campo correcto de la BD
                'telefono_prv' => 'required|string|max:20',

                'estado' => 'required|in:activo,inactivo',
            ]);

            // Actualizar proveedor
            $proveedor->update($datos);

            // Refrescar los datos después de actualizar
            $proveedor->refresh();

            return response()->json([
                'mensaje' => 'Proveedor actualizado correctamente',
                'proveedor' => $proveedor
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar proveedor físicamente
    public function destroy($id)
    {
        try {
            // Buscar proveedor
            $proveedor = Proveedor::find($id);

            // Verificar si existe
            if (!$proveedor) {
                return response()->json([
                    'mensaje' => 'Proveedor no encontrado'
                ], 404);
            }

            // Eliminar proveedor
            $proveedor->delete();

            return response()->json([
                'mensaje' => 'Proveedor eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

