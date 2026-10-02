<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    // Mostrar todos los roles (soporta ?search=)
    public function index(Request $request)
    {
        try {
            $query = Rol::query();

            if ($request->filled('search')) {
                $query->where('tipo_rol', 'like', "%{$request->query('search')}%");
            }

            $roles = $query->orderBy('id_rol')->get();

            return response()->json($roles, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un rol por ID
    public function show($id)
    {
        try {
            $rol = Rol::find($id);

            if (!$rol) {
                return response()->json([
                    'mensaje' => 'Rol no encontrado'
                ], 404);
            }

            return response()->json($rol, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo rol
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'tipo_rol' => 'required|string|max:50',
            ]);

            $rol = Rol::create($datos);

            return response()->json([
                'mensaje' => 'Rol creado correctamente',
                'rol' => $rol
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un rol
    public function update(Request $request, $id)
    {
        try {
            $rol = Rol::find($id);

            if (!$rol) {
                return response()->json([
                    'mensaje' => 'Rol no encontrado'
                ], 404);
            }

            $datos = $request->validate([
                'tipo_rol' => 'required|string|max:50',
            ]);

            $rol->update($datos);

            return response()->json([
                'mensaje' => 'Rol actualizado correctamente',
                'rol' => $rol
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un rol
    public function destroy($id)
    {
        try {
            $rol = Rol::find($id);

            if (!$rol) {
                return response()->json([
                    'mensaje' => 'Rol no encontrado'
                ], 404);
            }

            $rol->delete();

            return response()->json([
                'mensaje' => 'Rol eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
