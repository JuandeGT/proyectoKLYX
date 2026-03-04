<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; // Importamos tu controlador
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\CheckVipExpiration;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ObjetoController;
use App\Http\Controllers\CajaController;

// Rutas públicas (No hace falta estar logueado para usarlas)
Route::post('registro', [UserController::class, 'store']);
Route::post('login', [UserController::class, 'verify']);

// ZONA PROTEGIDA (Obligatorio mostrar el Token en la puerta)
Route::middleware(['auth:sanctum', CheckVipExpiration::class])->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    // Ruta para recargar Klyx Coins
    Route::post('recargar', [UserController::class, 'recargar']);

    // Ruta para comprar suscripción VIP
    Route::post('comprar-vip', [UserController::class, 'comprarSuscripcion']);

    Route::get('perfil', [ProfileController::class, 'show']);
    Route::put('erfil', [ProfileController::class, 'update']);

    // Historial de transacciones
    Route::get('mis-transacciones', [UserController::class, 'historialTransacciones']);

    // Historial de cajas
    Route::get('mis-cajas', [UserController::class, 'historialCajas']);

    // Inventario del usuario
    Route::get('mi-inventario', [UserController::class, 'miInventario']);

    // Los usuarios normales solo pueden ver la lista y ver una caja específica
    Route::apiResource('objetos', ObjetoController::class)->only(['index', 'show']);
    Route::apiResource('cajas', CajaController::class)->only(['index', 'show']);

    // Ruta para abrir una caja
    Route::post('cajas/{id}/abrir', [CajaController::class, 'abrir']);



    // Grupo solo para Administradores
    Route::middleware('role:Admin')->group(function () {
        // Esta línea crea automáticamente las rutas de ver, editar y borrar
        Route::apiResource('admin/usuarios', AdminUserController::class);

        // El Admin puede ver los historiales de cada usuario
        Route::get('admin/usuarios/{id}/cajas', [AdminUserController::class, 'historialCajas']);
        Route::get('admin/usuarios/{id}/transacciones', [AdminUserController::class, 'historialTransacciones']);

        // El Admin puede crear, editar y borrar cajas/objetos (exceptúa index y show porque ya son públicas)
        Route::apiResource('admin/objetos', ObjetoController::class)->except(['index', 'show']);
        Route::apiResource('admin/cajas', CajaController::class)->except(['index', 'show']);
        
        // El Admin puede meter y sacar armas de las cajas
        Route::post('admin/cajas/{id}/objetos', [CajaController::class, 'añadirObjeto']);
        Route::delete('admin/cajas/{id}/objetos/{objeto_id}', [CajaController::class, 'quitarObjeto']);
    });

});