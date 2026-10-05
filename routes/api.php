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
use App\Http\Controllers\Api\TipoDocumentoController;
use App\Http\Controllers\Api\TallaController;
use App\Http\Controllers\Api\LocalController;
use App\Http\Controllers\Api\MedidaController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\RolSecundarioController;
use App\Http\Controllers\Api\ReporteController;

// =========================================================
// RUTAS PÚBLICAS (no requieren sesión)
// =========================================================

// Registro: crea la cuenta de un cliente nuevo (rol_id = 4).
Route::post('/register', [AuthController::class, 'register']);

// Login: genera el JWT y lo guarda en una cookie HttpOnly.
Route::post('/login', [AuthController::class, 'login']);

// Consulta de usuario por email utilizada durante el login.
// No requiere JWT porque todavía no existe una sesión.
Route::get('/usuarios', [UsuarioController::class, 'index']);

// Ruta temporal para comprobar que React puede comunicarse con Laravel
Route::get('/prueba', function () {
    return response()->json([
        'ok' => true,
        'mensaje' => 'La API Laravel está funcionando correctamente'
    ]);
});

// Catálogo de productos: se consulta desde Inicio.jsx y el resto del
// vistaCliente antes de que exista una sesión iniciada.
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);


// =========================================================
// RUTAS PROTEGIDAS (requieren JWT válido en la cookie mjbe_token)
// Sin sesión iniciada responden 401 {"message":"Unauthenticated."}
// =========================================================
Route::middleware('auth:api')->group(function () {

    // Autenticación
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);


    // Dashboard
    Route::get('/reportes/dashboard', [ReporteController::class, 'dashboard']);


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
    Route::patch('/ventas/{id}', [VentaController::class, 'update']);


    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);


    // Productos (index/show son públicos, ver arriba)
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
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);


    // Tipos de documento
    Route::get('/tipos_documentos', [TipoDocumentoController::class, 'index']);
    Route::post('/tipos_documentos', [TipoDocumentoController::class, 'store']);
    Route::get('/tipos_documentos/{id}', [TipoDocumentoController::class, 'show']);
    Route::put('/tipos_documentos/{id}', [TipoDocumentoController::class, 'update']);
    Route::delete('/tipos_documentos/{id}', [TipoDocumentoController::class, 'destroy']);


    // Medidas
    Route::get('/medidas', [MedidaController::class, 'index']);
    Route::post('/medidas', [MedidaController::class, 'store']);
    Route::get('/medidas/{id}', [MedidaController::class, 'show']);
    Route::put('/medidas/{id}', [MedidaController::class, 'update']);
    Route::delete('/medidas/{id}', [MedidaController::class, 'destroy']);


    // Tallas
    Route::get('/tallas', [TallaController::class, 'index']);
    Route::post('/tallas', [TallaController::class, 'store']);
    Route::get('/tallas/{id}', [TallaController::class, 'show']);
    Route::put('/tallas/{id}', [TallaController::class, 'update']);
    Route::delete('/tallas/{id}', [TallaController::class, 'destroy']);


    // Locales
    Route::get('/locales', [LocalController::class, 'index']);
    Route::post('/locales', [LocalController::class, 'store']);
    Route::get('/locales/{id}', [LocalController::class, 'show']);
    Route::put('/locales/{id}', [LocalController::class, 'update']);
    Route::delete('/locales/{id}', [LocalController::class, 'destroy']);


    // Roles
    Route::get('/roles', [RolController::class, 'index']);
    Route::post('/roles', [RolController::class, 'store']);
    Route::get('/roles/{id}', [RolController::class, 'show']);
    Route::put('/roles/{id}', [RolController::class, 'update']);
    Route::delete('/roles/{id}', [RolController::class, 'destroy']);


    // Roles secundarios
    Route::get('/roles_secundarios', [RolSecundarioController::class, 'index']);
    Route::post('/roles_secundarios', [RolSecundarioController::class, 'store']);
    Route::delete('/roles_secundarios/{usuario_id}/{rol_secundario}', [RolSecundarioController::class, 'destroy']);

});
