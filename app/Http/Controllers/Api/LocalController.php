<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Local;
use Illuminate\Http\Request;

class LocalController extends Controller
{
    // Columnas para ?search=
    private array $camposBusqueda = [
        'nombre_local',
        'direccion_local',
        'correo',
        'telefono',
        'estado',
    ];

    // Mostrar todos los locales (soporta ?search= y ?estado=)
    public function index(Request $request)
    {
        try {
            $query = Local::query();

            if ($request->filled('search')) {
                $busqueda = $request->query('search');

                $query->where(function ($q) use ($busqueda) {
                    foreach ($this->camposBusqueda as $campo) {
                        $q->orWhere($campo, 'like', "%{$busqueda}%");
                    }
                });
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->query('estado'));
            }

            $locales = $query->orderBy('id')->get();

            return response()->json($locales, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener los locales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mostrar un local por ID
    public function show($id)
    {
        try {
            $local = Local::find($id);

            if (!$local) {
                return response()->json([
                    'mensaje' => 'Local no encontrado'
                ], 404);
            }

            return response()->json($local, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener el local',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo local
    public function store(Request $request)
    {
        try {
            $datos = $request->validate([
                'nombre_local' => 'required|string|max:100',
                'direccion_local' => 'required|string|max:150',
                'correo' => 'required|email|max:100',
                'telefono' => 'required|string|max:20',
                'estado' => 'nullable|in:activo,inactivo',
            ]);

            // Si no se envía estado, por defecto queda activo.
            $datos['estado'] = $datos['estado'] ?? 'activo';

            $local = Local::create($datos);

            return response()->json([
                'mensaje' => 'Local creado correctamente',
                'local' => $local
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al crear el local',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un local
    public function update(Request $request, $id)
    {
        try {
            $local = Local::find($id);

            if (!$local) {
                return response()->json([
                    'mensaje' => 'Local no encontrado'
                ], 404);
            }

            $datos = $request->validate([
                'nombre_local' => 'required|string|max:100',
                'direccion_local' => 'required|string|max:150',
                'correo' => 'required|email|max:100',
                'telefono' => 'required|string|max:20',
                'estado' => 'required|in:activo,inactivo',
            ]);

            $local->update($datos);

            return response()->json([
                'mensaje' => 'Local actualizado correctamente',
                'local' => $local
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar el local',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un local
    public function destroy($id)
    {
        try {
            $local = Local::find($id);

            if (!$local) {
                return response()->json([
                    'mensaje' => 'Local no encontrado'
                ], 404);
            }

            $local->delete();

            return response()->json([
                'mensaje' => 'Local eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el local',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
