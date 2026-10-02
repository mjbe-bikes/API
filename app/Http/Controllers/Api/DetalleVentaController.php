<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;

class DetalleVentaController extends Controller
{
    /**
     * =========================================================
     * LISTAR TODOS LOS DETALLES
     * =========================================================
     *
     * GET /api/detalles_venta
     */
    public function index()
    {
        try {

            /*
             * Obtener los detalles junto con el producto
             * relacionado.
             */
            $detalles = DetalleVenta::with('producto')->get();

            return response()->json($detalles, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar los detalles de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * LISTAR DETALLES DE UNA VENTA
     * =========================================================
     *
     * GET /api/detalles_venta?id_venta=1
     */
    public function porVenta(Request $request)
    {
        try {

            /*
             * Validar que se haya enviado el ID de la venta.
             */
            $datos = $request->validate([
                'id_venta' =>
                    'required|integer|exists:ventas,id',
            ]);

            /*
             * Buscar los productos pertenecientes
             * a esa venta.
             */
            $detalles = DetalleVenta::with('producto')
                ->where(
                    'id_venta',
                    $datos['id_venta']
                )
                ->get();

            return response()->json($detalles, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar los detalles de la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * MOSTRAR UN DETALLE
     * =========================================================
     *
     * GET /api/detalles_venta/{id}
     */
    public function show($id)
    {
        try {

            /*
             * Buscar el detalle junto con su producto.
             */
            $detalle = DetalleVenta::with('producto')->find($id);

            /*
             * Verificar si existe.
             */
            if (!$detalle) {

                return response()->json([
                    'mensaje' => 'Detalle de venta no encontrado'
                ], 404);
            }

            return response()->json($detalle, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar el detalle de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * CREAR DETALLE DE VENTA
     * =========================================================
     *
     * POST /api/detalles_venta
     *
     * CompraCliente.jsx envía:
     *
     * {
     *     id_venta,
     *     id_producto,
     *     cantidad,
     *     precio_unitario_momento
     * }
     */
    public function store(Request $request)
    {
        try {

            /*
             * Validar los datos.
             *
             * id_venta debe existir en ventas.
             * id_producto debe existir en productos.
             */
            $datos = $request->validate([

                'id_venta' =>
                    'required|integer|exists:ventas,id',

                'id_producto' =>
                    'required|integer|exists:productos,id',

                'cantidad' =>
                    'required|integer|min:1',

                'precio_unitario_momento' =>
                    'required|numeric|min:0',
            ]);

            /*
             * Crear el detalle.
             */
            $detalle = DetalleVenta::create($datos);

            /*
             * Cargar el producto para devolver información
             * completa al frontend.
             */
            $detalle->load('producto');

            return response()->json([
                'mensaje' => 'Detalle de venta creado correctamente',
                'detalle' => $detalle
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al crear el detalle de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

