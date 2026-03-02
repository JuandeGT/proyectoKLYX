<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; // Importamos tu controlador
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\CheckVipExpiration;
use App\Http\Controllers\ProfileController;

// Rutas públicas (No hace falta estar logueado para usarlas)
Route::post('/registro', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'verify']);

// ZONA PROTEGIDA (Obligatorio mostrar el Token en la puerta)
Route::middleware(['auth:sanctum', CheckVipExpiration::class])->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);

    // Ruta para recargar Klyx Coins
    Route::post('/recargar', [UserController::class, 'recargar']);

    // Ruta para comprar suscripción VIP
    Route::post('/comprar-vip', [UserController::class, 'comprarSuscripcion']);

    Route::get('/perfil', [ProfileController::class, 'show']);
    Route::put('/perfil', [ProfileController::class, 'update']);

    // Grupo solo para Administradores
    Route::middleware('role:Admin')->group(function () {
        // Esta línea crea automáticamente las rutas de ver, editar y borrar
        Route::apiResource('admin/usuarios', AdminUserController::class);
    });

});