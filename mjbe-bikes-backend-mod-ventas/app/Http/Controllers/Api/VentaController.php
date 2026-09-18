<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Cliente;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    /**
     * =========================================================
     * LISTAR VENTAS
     * =========================================================
     *
     * GET /api/ventas
     *
     * También permite:
     *
     * GET /api/ventas?user_id=5
     *
     * Cuando se recibe user_id:
     *
     * usuarios.id
     *      ↓
     * clientes.usuario_id
     *      ↓
     * ventas.id_cliente
     *
     * Esto permite que MisCompras.jsx consulte únicamente
     * las compras del usuario que inició sesión.
     */
    public function index(Request $request)
    {
        try {

            /*
             * Cargar todas las relaciones necesarias.
             *
             * cliente:
             *    información del cliente de la venta.
             *
             * vendedor:
             *    información del usuario que realizó la venta.
             *
             * detalles.producto:
             *    productos incluidos en la venta.
             */
            $consulta = Venta::with([
                'cliente',
                'vendedor',
                'detalles.producto'
            ]);

            /*
             * FILTRAR COMPRAS DE UN USUARIO
             *
             * user_id corresponde a usuarios.id.
             *
             * Primero buscamos el cliente relacionado
             * con ese usuario.
             */
            if ($request->filled('user_id')) {

                $cliente = Cliente::where(
                    'usuario_id',
                    $request->query('user_id')
                )->first();

                /*
                 * Si el usuario no tiene un registro en clientes,
                 * entonces no tiene compras asociadas.
                 */
                if (!$cliente) {
                    return response()->json([]);
                }

                /*
                 * ventas.id_cliente corresponde a clientes.id.
                 */
                $consulta->where(
                    'id_cliente',
                    $cliente->id
                );
            }

            /*
             * Mostrar primero las ventas más recientes.
             */
            $ventas = $consulta
                ->orderByDesc('fecha')
                ->get();

            /*
             * Utilizamos el mismo formateador para todas
             * las respuestas del módulo de ventas.
             */
            $resultado = $ventas->map(function ($venta) {
                return $this->formatearVenta($venta);
            });

            return response()->json($resultado, 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar las ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * CREAR VENTA
     * =========================================================
     *
     * POST /api/ventas
     *
     * Utilizado por CrearVenta.jsx del módulo de vendedores.
     *
     * El frontend envía:
     *
     * {
     *     id_vendedor,
     *     id_cliente,
     *     fecha,
     *     total,
     *     pago
     * }
     *
     * Los productos se registran posteriormente mediante:
     *
     * POST /api/detalles_venta
     */
    public function store(Request $request)
    {
        try {

            /*
             * Validar exactamente los campos enviados
             * por CrearVenta.jsx.
             */
            $datos = $request->validate([

                // Usuario vendedor que realiza la venta.
                'id_vendedor' =>
                    'required|integer|exists:usuarios,id',

                // Cliente asociado a la venta.
                'id_cliente' =>
                    'required|integer|exists:clientes,id',

                // Fecha de la venta.
                'fecha' =>
                    'required|date',

                // Total calculado por React.
                'total' =>
                    'required|numeric|min:0',

                // Estado de pago almacenado en la BD.
                'pago' =>
                    'required|boolean',
            ]);

            /*
             * Crear la venta utilizando los nombres reales
             * de las columnas de la tabla ventas.
             */
            $venta = Venta::create([

                'id_vendedor' => $datos['id_vendedor'],

                'id_cliente' => $datos['id_cliente'],

                'fecha' => $datos['fecha'],

                'total' => $datos['total'],

                'pago' => $datos['pago'],
            ]);

            /*
             * Cargar las relaciones necesarias después
             * de crear la venta.
             */
            $venta->load([
                'cliente',
                'vendedor',
                'detalles.producto'
            ]);

            /*
             * Devolver directamente la venta creada.
             *
             * Esto coincide con CrearVenta.jsx:
             *
             * const ventaCreada = datosVenta;
             *
             * y:
             *
             * ventaCreada.id
             */
            return response()->json(
                $this->formatearVenta($venta),
                201
            );

        } catch (\Illuminate\Validation\ValidationException $e) {

            /*
             * Error de validación.
             */
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            /*
             * Cualquier otro error del servidor.
             */
            return response()->json([
                'mensaje' => 'Error al crear la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * MOSTRAR UNA VENTA
     * =========================================================
     *
     * GET /api/ventas/{id}
     */
    public function show($id)
    {
        try {

            /*
             * Buscar la venta junto con:
             *
             * - cliente
             * - vendedor
             * - detalles
             * - productos
             */
            $venta = Venta::with([
                'cliente',
                'vendedor',
                'detalles.producto'
            ])->find($id);

            /*
             * Si no existe la venta.
             */
            if (!$venta) {

                return response()->json([
                    'mensaje' => 'Venta no encontrada'
                ], 404);
            }

            /*
             * Utilizar exactamente el mismo formato
             * utilizado por index() y store().
             */
            return response()->json(
                $this->formatearVenta($venta),
                200
            );

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al consultar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * =========================================================
     * FORMATEAR VENTA
     * =========================================================
     *
     * Centraliza la estructura de respuesta de:
     *
     * - index()
     * - store()
     * - show()
     *
     * Así evitamos repetir la misma lógica.
     */
    private function formatearVenta(Venta $venta)
    {
        /*
         * Convertir los detalles en una lista de productos
         * fácil de utilizar desde React.
         */
        $productos = $venta->detalles->map(function ($detalle) {

            /*
             * Primero utilizamos el precio que se guardó
             * en el momento de la venta.
             *
             * Si por alguna razón es null, utilizamos
             * el precio actual del producto.
             */
            $precio = $detalle->precio_unitario_momento
                ?? $detalle->producto->valor_unitario
                ?? 0;

            return [

                // ID del producto.
                'id' => $detalle->id_producto,

                // Mantener también el nombre explícito.
                'id_producto' => $detalle->id_producto,

                // Nombre del producto.
                'nombre_producto' =>
                    $detalle->producto->nombre_producto
                    ?? 'Producto',

                // Cantidad comprada.
                'cantidad' => $detalle->cantidad,

                /*
                 * Precio utilizado en el momento de la venta.
                 */
                'valor_unitario' => $precio,

                /*
                 * Nombre alternativo utilizado por
                 * DetalleVentaController.
                 */
                'precio_unitario_momento' => $precio,

                // Subtotal del producto.
                'subtotal' =>
                    (float) $detalle->cantidad *
                    (float) $precio,
            ];
        })->values();


        /*
         * La tabla ventas tiene "pago", mientras que
         * algunas vistas utilizan "estado".
         *
         * Convertimos el booleano al texto esperado
         * por el frontend.
         */
        $estado = $venta->pago
            ? 'completada'
            : 'pendiente';


        /*
         * Estructura común para todas las pantallas.
         */
        return [

            // ID de la venta.
            'id' => $venta->id,

            /*
             * ID del usuario que figura como vendedor.
             */
            'id_vendedor' => $venta->id_vendedor,

            /*
             * ID de clientes.id.
             */
            'id_cliente' => $venta->id_cliente,

            // Fecha de la venta.
            'fecha' => $venta->fecha,

            // Total.
            'total' => (float) $venta->total,

            /*
             * Estado compatible con React.
             */
            'estado' => $estado,

            /*
             * Valor real almacenado en la BD.
             */
            'pago' => (bool) $venta->pago,

            /*
             * Actualmente la tabla ventas no tiene
             * una columna metodo_pago.
             */
            'metodo_pago' => null,

            /*
             * Cliente completo.
             */
            'cliente' => $venta->cliente,

            /*
             * Vendedor completo.
             */
            'vendedor' => $venta->vendedor,

            /*
             * Productos comprados.
             */
            'productos' => $productos,

            /*
             * Detalles originales de la venta.
             */
            'detalles' => $venta->detalles,
        ];
    }
}

