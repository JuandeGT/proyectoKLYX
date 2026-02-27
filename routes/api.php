<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; // Importamos tu controlador

// Rutas públicas (No hace falta estar logueado para usarlas)
Route::post('/registro', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'verify']);