<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolSecundario;
use Illuminate\Http\Request;

class RolSecundarioController extends Controller
{
    // Mostrar todos los roles secundarios (soporta ?usuario_id=)
    public function index(Request $request)
    {
        try {
            $query = RolSecundario::with(['usuario', 'rol']);

            if ($request->filled('usuario_id')) {
                $query->where('usuario_id', $request->query('usuario_id'));
            }

            $rolesSecundarios = $query->orderBy('usuario_id')->get();

            return response()->json($rolesSecundarios, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los roles secundarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Asignar un rol secundario a un usuario
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'usuario_id' => 'required|integer|exists:usuarios,id',
                'rol_secundario' => 'required|integer|exists:roles,id_rol',
            ]);

            $existente = RolSecundario::where('usuario_id', $datos['usuario_id'])
                ->where('rol_secundario', $datos['rol_secundario'])
                ->exists();

            if ($existente) {
                return response()->json([
                    'mensaje' => 'El usuario ya tiene asignado ese rol secundario'
                ], 400);
            }

            $rolSecundario = RolSecundario::create($datos);

            return response()->json([
                'mensaje' => 'Rol secundario asignado correctamente',
                'rol_secundario' => $rolSecundario
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al asignar el rol secundario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Quitar un rol secundario a un usuario
    public function destroy($usuario_id, $rol_secundario)
    {
        try {
            $eliminado = RolSecundario::where('usuario_id', $usuario_id)
                ->where('rol_secundario', $rol_secundario)
                ->delete();

            if (!$eliminado) {
                return response()->json([
                    'mensaje' => 'Asignación no encontrada'
                ], 404);
            }

            return response()->json([
                'mensaje' => 'Rol secundario eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el rol secundario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
