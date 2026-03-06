<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\CheckVipExpiration;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ObjetoController;
use App\Http\Controllers\CajaController;

Route::post('registro', [UserController::class, 'store']);
Route::post('login', [UserController::class, 'verify']);

Route::apiResource('objetos', ObjetoController::class)->only(['index', 'show']);
Route::apiResource('cajas', CajaController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', CheckVipExpiration::class])->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('recargar', [UserController::class, 'recargar']);
    Route::post('comprar-vip', [UserController::class, 'comprarSuscripcion']);

    Route::get('perfil', [ProfileController::class, 'show']);
    Route::put('perfil', [ProfileController::class, 'update']);

    Route::get('mis-transacciones', [UserController::class, 'historialTransacciones']);
    Route::get('mis-cajas', [UserController::class, 'historialCajas']);
    Route::get('mi-inventario', [UserController::class, 'miInventario']);

    Route::post('cajas/{id}/abrir', [CajaController::class, 'abrir']);



    Route::middleware('role:Admin')->group(function () {
        Route::apiResource('admin/usuarios', AdminUserController::class);

        Route::get('admin/usuarios/{id}/cajas', [AdminUserController::class, 'historialCajas']);
        Route::get('admin/usuarios/{id}/transacciones', [AdminUserController::class, 'historialTransacciones']);

        Route::apiResource('admin/objetos', ObjetoController::class)->except(['index', 'show']);
        Route::apiResource('admin/cajas', CajaController::class)->except(['index', 'show']);
        
        Route::post('admin/cajas/{id}/objetos', [CajaController::class, 'añadirObjeto']);
        Route::delete('admin/cajas/{id}/objetos/{objeto_id}', [CajaController::class, 'quitarObjeto']);
    });

});