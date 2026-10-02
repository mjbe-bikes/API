<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medida;
use Illuminate\Http\Request;

class MedidaController extends Controller
{
    // Mostrar todas las medidas (soporta ?search=)
    public function index(Request $request)
    {
        try {
            $query = Medida::query();

            if ($request->filled('search')) {
                $query->where('tipo_medida', 'like', "%{$request->query('search')}%");
            }

            $medidas = $query->orderBy('id')->get();

            return response()->json($medidas, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener las medidas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar una medida por ID
    public function show($id)
    {
        try {
            $medida = Medida::find($id);

            if (!$medida) {
                return response()->json([
                    'mensaje' => 'Medida no encontrada'
                ], 404);
            }

            return response()->json($medida, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener la medida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear una nueva medida
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'tipo_medida' => 'required|string|max:100',
            ]);

            $medida = Medida::create($datos);

            return response()->json([
                'mensaje' => 'Medida creada correctamente',
                'medida' => $medida
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear la medida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar una medida
    public function update(Request $request, $id)
    {
        try {
            $medida = Medida::find($id);

            if (!$medida) {
                return response()->json([
                    'mensaje' => 'Medida no encontrada'
                ], 404);
            }

            $datos = $request->validate([
                'tipo_medida' => 'required|string|max:100',
            ]);

            $medida->update($datos);

            return response()->json([
                'mensaje' => 'Medida actualizada correctamente',
                'medida' => $medida
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la medida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar una medida
    public function destroy($id)
    {
        try {
            $medida = Medida::find($id);

            if (!$medida) {
                return response()->json([
                    'mensaje' => 'Medida no encontrada'
                ], 404);
            }

            $medida->delete();

            return response()->json([
                'mensaje' => 'Medida eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la medida',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
