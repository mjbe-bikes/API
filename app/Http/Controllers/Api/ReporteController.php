<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;

class ReporteController extends Controller
{
    /**
     * =========================================================
     * DASHBOARD DEL ADMINISTRADOR
     * =========================================================
     *
     * GET /api/reportes/dashboard
     *
     * Devuelve la misma forma que espera InicioAdmin.jsx:
     * resumen, reportes (últimas ventas) y novedades.
     */
    public function dashboard()
    {
        try {
            $hoy = Carbon::today();

            $resumen = [
                'totalProductos' => Producto::count(),
                'totalClientes' => Cliente::count(),
                'totalVentas' => Venta::count(),
                'ventasHoy' => Venta::whereDate('fecha', $hoy)->count(),
                'ventasMes' => Venta::whereYear('fecha', $hoy->year)
                    ->whereMonth('fecha', $hoy->month)
                    ->count(),
                'stockBajo' => Producto::where('cant_producto', '<=', 5)->count(),
                'ingresosMes' => (float) Venta::whereYear('fecha', $hoy->year)
                    ->whereMonth('fecha', $hoy->month)
                    ->sum('total'),
            ];

            // Las 5 ventas más recientes, con el nombre del cliente.
            $ultimasVentas = Venta::with('cliente')
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            $reportes = $ultimasVentas->map(function ($venta) {
                $cliente = $venta->cliente
                    ? trim($venta->cliente->nombres . ' ' . $venta->cliente->apellidos)
                    : 'N/D';

                return [
                    'id' => $venta->id,
                    'titulo' => "Venta #{$venta->id}",
                    'descripcion' => "Cliente: {$cliente} - Total: $" . number_format((float) $venta->total, 0, ',', '.'),
                    'fecha' => $venta->fecha,
                ];
            })->values();

            return response()->json([
                'resumen' => $resumen,
                'reportes' => $reportes,
                'novedades' => [],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'mensaje' => 'Error al obtener el dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
