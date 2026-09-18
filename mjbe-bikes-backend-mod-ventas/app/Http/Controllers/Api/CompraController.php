<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    // Obtener todas las compras
    public function index()
    {
        $compras = Compra::orderByDesc('id')->get();

        return response()->json($compras);
    }

    // Crear una nueva compra
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $datos = $request->validate([
            'id_proveedor' => 'required|integer|exists:proveedores,id',
            'fecha' => 'nullable|date',
            'total_compra' => 'required|numeric|min:0',
            'pago' => 'required|boolean',
        ]);

        // Crear la compra
        $compra = Compra::create($datos);

        return response()->json($compra, 201);
    }

    // Obtener una compra específica
    public function show($id)
    {
        $compra = Compra::findOrFail($id);

        return response()->json($compra);
    }
}