<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\CheckVipExpiration;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ObjetoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\IntercambioController;
 

// Comprueba que el servidor está vivo, útil para Render y para que el frontend sepa si la API responde
Route::get('ping', fn() => response()->json(['message' => 'ok'], 200));

Route::post('registro', [UserController::class, 'store']);
Route::post('login',    [UserController::class, 'verify']);

Route::apiResource('objetos', ObjetoController::class)->only(['index', 'show']);
Route::apiResource('cajas',   CajaController::class)->only(['index', 'show']);

Route::get('oferta-semanal',   [ObjetoController::class, 'ofertaSemanal']);
Route::get('historial-objetos', [ObjetoController::class, 'historialPublico']);


Route::middleware(['auth:sanctum', CheckVipExpiration::class])->group(function () {

    Route::post('logout',  [UserController::class, 'logout']);
    Route::delete('cuenta', [UserController::class, 'eliminarCuenta']);

    Route::post('recargar',    [UserController::class, 'recargar']);
    Route::post('comprar-vip', [UserController::class, 'comprarSuscripcion']);

    Route::get('perfil', [ProfileController::class, 'show']);
    Route::put('perfil', [ProfileController::class, 'update']);

    Route::get('mis-transacciones', [UserController::class, 'historialTransacciones']);
    Route::get('mis-cajas',         [UserController::class, 'historialCajas']);
    Route::get('mi-inventario',     [UserController::class, 'miInventario']);

    Route::post('cajas/{id}/abrir', [CajaController::class, 'abrir']);

    // AÑADIDO: compra directa de un objeto (usado en la sección Oferta Semanal del frontend)
    Route::post('objetos/{id}/comprar-directo', [ObjetoController::class, 'comprarObjetoDirecto']);

    // Sistema de intercambios
    Route::get('intercambios',                  [IntercambioController::class, 'index']);
    Route::get('mis-intercambios',              [IntercambioController::class, 'misOfertas']);
    Route::post('intercambios',                 [IntercambioController::class, 'store']);
    Route::put('intercambios/{id}/aceptar',     [IntercambioController::class, 'aceptar']);
    Route::put('intercambios/{id}/rechazar',    [IntercambioController::class, 'rechazar']);
    Route::delete('intercambios/{id}',          [IntercambioController::class, 'destroy']);


    Route::middleware('role:Admin')->group(function () {

        Route::apiResource('admin/usuarios', AdminUserController::class);
        Route::get('admin/usuarios/{id}/cajas',        [AdminUserController::class, 'historialCajas']);
        Route::get('admin/usuarios/{id}/transacciones',[AdminUserController::class, 'historialTransacciones']);

        Route::get('admin/estadisticas', [AdminUserController::class, 'estadisticas']);

        Route::get('admin/intercambios', [IntercambioController::class, 'todosIntercambios']);

        Route::apiResource('admin/objetos', ObjetoController::class)->except(['index', 'show']);
        Route::put('admin/objetos/{id}/oferta', [ObjetoController::class, 'toggleOferta']);

        Route::apiResource('admin/cajas', CajaController::class)->except(['index', 'show']);
        Route::post('admin/cajas/{id}/objetos',                 [CajaController::class, 'añadirObjeto']);
        Route::delete('admin/cajas/{id}/objetos/{objeto_id}',   [CajaController::class, 'quitarObjeto']);
    });
});
