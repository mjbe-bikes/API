<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\DetalleVentaController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\DetalleCompraController;
use App\Http\Controllers\Api\AuthController;

// =========================================================
// AUTENTICACIÓN JWT xd
// =========================================================

// Login: genera el JWT y lo guarda en una cookie HttpOnly.
Route::post('/login', [AuthController::class, 'login']);

// Comprobar usuario autenticado.
// Esta ruta necesita un JWT válido.
Route::get('/me', [AuthController::class, 'me'])
    ->middleware('auth:api');

// Cerrar sesión.
// También necesita un JWT válido.
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:api');


// Compras
Route::get('/compras', [CompraController::class, 'index']);
Route::post('/compras', [CompraController::class, 'store']);
Route::get('/compras/{id}', [CompraController::class, 'show']);

// Detalles de compra
Route::get('/detalles_compra', [DetalleCompraController::class, 'index']);
Route::post('/detalles_compra', [DetalleCompraController::class, 'store']);


// Ventas
Route::get('/ventas', [VentaController::class, 'index']);
Route::post('/ventas', [VentaController::class, 'store']);
Route::get('/ventas/{id}', [VentaController::class, 'show']);


// Clientes
Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);


// Productos
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::put('/productos/{id}', [ProductoController::class, 'update']);
Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
Route::post('/productos', [ProductoController::class, 'store']);


// Detalles de venta
Route::get('/detalles_venta', [DetalleVentaController::class, 'porVenta']);
Route::get('/detalles_venta/{id}', [DetalleVentaController::class, 'show']);
Route::post('/detalles_venta', [DetalleVentaController::class, 'store']);


// Proveedores
Route::get('/proveedores', [ProveedorController::class, 'index']);
Route::post('/proveedores', [ProveedorController::class, 'store']);
Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);


// Usuarios
Route::get('/usuarios', [UsuarioController::class, 'index']);
Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
Route::post('/usuarios', [UsuarioController::class, 'store']);
Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);


// Ruta temporal para comprobar que React puede comunicarse con Laravel
Route::get('/prueba', function () {
    return response()->json([
        'ok' => true,
        'mensaje' => 'La API Laravel está funcionando correctamente'
    ]);
});