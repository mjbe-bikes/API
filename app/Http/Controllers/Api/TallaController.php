<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Talla;
use Illuminate\Http\Request;

class TallaController extends Controller
{
    // Mostrar todas las tallas (soporta ?search= y ?medida_id=)
    public function index(Request $request)
    {
        try {
            $query = Talla::with('medida');

            if ($request->filled('search')) {
                $query->where('talla', 'like', "%{$request->query('search')}%");
            }

            if ($request->filled('medida_id')) {
                $query->where('medida_id', $request->query('medida_id'));
            }

            $tallas = $query->orderBy('id')->get();

            return response()->json($tallas, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener las tallas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar una talla por ID
    public function show($id)
    {
        try {
            $talla = Talla::with('medida')->find($id);

            if (!$talla) {
                return response()->json([
                    'mensaje' => 'Talla no encontrada'
                ], 404);
            }

            return response()->json($talla, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener la talla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear una nueva talla
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'medida_id' => 'required|integer|exists:medidas,id',
                'talla' => 'required|string|max:50',
            ]);

            $talla = Talla::create($datos);
            $talla->load('medida');

            return response()->json([
                'mensaje' => 'Talla creada correctamente',
                'talla' => $talla
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear la talla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar una talla
    public function update(Request $request, $id)
    {
        try {
            $talla = Talla::find($id);

            if (!$talla) {
                return response()->json([
                    'mensaje' => 'Talla no encontrada'
                ], 404);
            }

            $datos = $request->validate([
                'medida_id' => 'required|integer|exists:medidas,id',
                'talla' => 'required|string|max:50',
            ]);

            $talla->update($datos);
            $talla->load('medida');

            return response()->json([
                'mensaje' => 'Talla actualizada correctamente',
                'talla' => $talla
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la talla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar una talla
    public function destroy($id)
    {
        try {
            $talla = Talla::find($id);

            if (!$talla) {
                return response()->json([
                    'mensaje' => 'Talla no encontrada'
                ], 404);
            }

            $talla->delete();

            return response()->json([
                'mensaje' => 'Talla eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la talla',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
