<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    // Mostrar todos los tipos de documento (soporta ?search=)
    public function index(Request $request)
    {
        try {
            $query = TipoDocumento::query();

            if ($request->filled('search')) {
                $busqueda = $request->query('search');

                $query->where(function ($q) use ($busqueda) {
                    $q->where('sigla', 'like', "%{$busqueda}%")
                        ->orWhere('nombre_documento', 'like', "%{$busqueda}%");
                });
            }

            $tiposDocumentos = $query->orderBy('id')->get();

            return response()->json($tiposDocumentos, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los tipos de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un tipo de documento por ID
    public function show($id)
    {
        try {
            $tipoDocumento = TipoDocumento::find($id);

            if (!$tipoDocumento) {
                return response()->json([
                    'mensaje' => 'Tipo de documento no encontrado'
                ], 404);
            }

            return response()->json($tipoDocumento, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener el tipo de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo tipo de documento
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'sigla' => 'required|string|max:20',
                'nombre_documento' => 'required|string|max:100',
            ]);

            $tipoDocumento = TipoDocumento::create($datos);

            return response()->json([
                'mensaje' => 'Tipo de documento creado correctamente',
                'tipo_documento' => $tipoDocumento
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear el tipo de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un tipo de documento
    public function update(Request $request, $id)
    {
        try {
            $tipoDocumento = TipoDocumento::find($id);

            if (!$tipoDocumento) {
                return response()->json([
                    'mensaje' => 'Tipo de documento no encontrado'
                ], 404);
            }

            $datos = $request->validate([
                'sigla' => 'required|string|max:20',
                'nombre_documento' => 'required|string|max:100',
            ]);

            $tipoDocumento->update($datos);

            return response()->json([
                'mensaje' => 'Tipo de documento actualizado correctamente',
                'tipo_documento' => $tipoDocumento
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar el tipo de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un tipo de documento
    public function destroy($id)
    {
        try {
            $tipoDocumento = TipoDocumento::find($id);

            if (!$tipoDocumento) {
                return response()->json([
                    'mensaje' => 'Tipo de documento no encontrado'
                ], 404);
            }

            $tipoDocumento->delete();

            return response()->json([
                'mensaje' => 'Tipo de documento eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el tipo de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
