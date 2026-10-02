<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Utilizado actualmente por CompraCliente.jsx.
     */
    public function store(Request $request)
    {
        try {

            /*
             * Validar los datos enviados por React.
             */
            $datos = $request->validate([

                // Usuario que realiza la compra.
                'user_id' => 'required|integer|exists:usuarios,id',

                // ID real de clientes.id.
                'cliente' => 'required|integer|exists:clientes,id',

                // Productos del carrito.
                'productos' => 'required|array|min:1',
                'productos.*.id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.valor_unitario' => 'required|numeric|min:0',

                // Total de la compra.
                'total' => 'required|numeric|min:0',

                /*
                 * Se reciben porque forman parte del formulario
                 * actual del frontend.
                 *
                 * No se almacenan porque estas columnas
                 * no existen en la tabla ventas.
                 */
                'direccion' => 'required|string|max:255',

                'metodo_pago' =>
                    'required|in:efectivo,transferencia,tarjeta,contraentrega',

                /*
                 * Estados manejados por el frontend.
                 */
                'estado' =>
                    'required|in:pendiente,completada,cancelada',

                // Fecha enviada desde React.
                'fecha' => 'required|date',
            ]);

            /*
             * La BD utiliza el campo "pago" como booleano.
             *
             * completada → true
             * pendiente   → false
             * cancelada   → false
             */
            $pago = $datos['estado'] === 'completada';

            /*
             * Crear la venta junto con sus detalles dentro de una
             * transacción: si algo falla, no queda una venta sin
             * sus productos ni un descuento de stock a medias.
             *
             * Actualmente el frontend envía user_id como
             * usuario que realiza la operación.
             */
            $venta = DB::transaction(function () use ($datos, $pago) {

                $venta = Venta::create([

                    'id_vendedor' => $datos['user_id'],

                    'id_cliente' => $datos['cliente'],

                    'fecha' => $datos['fecha'],

                    'total' => $datos['total'],

                    'pago' => $pago,
                ]);

                /*
                 * Crear un detalle por cada producto del carrito.
                 * DetalleVenta descuenta el stock automáticamente
                 * al crearse (ver App\Models\DetalleVenta::booted()).
                 */
                foreach ($datos['productos'] as $item) {
                    DetalleVenta::create([
                        'id_venta' => $venta->id,
                        'id_producto' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario_momento' => $item['valor_unitario'],
                    ]);
                }

                return $venta;
            });

            /*
             * Cargar las relaciones necesarias después de crear
             * la venta.
             */
            $venta->load([
                'cliente',
                'vendedor',
                'detalles.producto'
            ]);

            /*
             * Devolver la misma estructura que utilizan
             * index() y show().
             */
            return response()->json([
                'mensaje' => 'Venta creada correctamente.',
                'venta' => $this->formatearVenta($venta),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

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
     * ACTUALIZAR ESTADO DE PAGO
     * =========================================================
     *
     * PATCH /api/ventas/{id}
     *
     * Utilizado por CompraCliente.jsx para confirmar el pago
     * simulado una vez finalizado el checkout.
     */
    public function update(Request $request, $id)
    {
        try {

            $venta = Venta::find($id);

            if (!$venta) {

                return response()->json([
                    'mensaje' => 'Venta no encontrada'
                ], 404);
            }

            $datos = $request->validate([
                'pago' => 'required|boolean',
            ]);

            $venta->update($datos);

            $venta->load([
                'cliente',
                'vendedor',
                'detalles.producto'
            ]);

            return response()->json(
                $this->formatearVenta($venta),
                200
            );

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al actualizar la venta',
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
             * en el momento de realizar la venta.
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
             *
             * Lo necesitan InicioVentas y Historialventas
             * para filtrar las ventas del vendedor.
             */
            'id_vendedor' => $venta->id_vendedor,

            /*
             * ID de clientes.id.
             *
             * Lo utilizan las vistas para relacionar
             * la venta con el cliente.
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
             *
             * Ejemplo:
             * venta.cliente.nombres
             * venta.cliente.apellidos
             */
            'cliente' => $venta->cliente,

            /*
             * Vendedor completo.
             *
             * Ejemplo:
             * venta.vendedor.login
             */
            'vendedor' => $venta->vendedor,

            /*
             * Productos comprados.
             */
            'productos' => $productos,

            /*
             * Detalles originales de la venta.
             *
             * Esto permite que InformacionVenta.jsx
             * tenga acceso a información adicional.
             */
            'detalles' => $venta->detalles,
        ];
    }
}
