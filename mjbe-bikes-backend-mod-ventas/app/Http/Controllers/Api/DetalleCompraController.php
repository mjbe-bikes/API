<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;

class DetalleCompraController extends Controller
{
    // Obtener los detalles de las compras
    public function index(Request $request)
    {
        $query = DetalleCompra::query();

        // Si se recibe id_compra, filtrar los detalles
        if ($request->filled('id_compra')) {

            $request->validate([
                'id_compra' => 'required|integer|exists:compras,id',
            ]);

            $query->where('id_compra', $request->id_compra);
        }

        $detalles = $query->orderBy('id')->get();

        return response()->json($detalles);
    }

    // Crear un detalle de compra
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $datos = $request->validate([
            'id_compra' => 'required|integer|exists:compras,id',
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario_momento' => 'required|numeric|min:0',
        ]);

        // Crear el detalle
        $detalle = DetalleCompra::create($datos);

        return response()->json($detalle, 201);
    }
}