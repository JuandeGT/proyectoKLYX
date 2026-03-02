<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVipExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obtenemos al usuario que está haciendo la petición
        $user = $request->user();

        // 2. Si el usuario existe y tiene la suscripción activa...
        if ($user && $user->suscripcion) {
            
            // 3. Comprobamos si la fecha de fin es MENOR que la fecha y hora actual
            if ($user->fecha_fin_suscripcion && $user->fecha_fin_suscripcion < now()) {
                
                // ¡Se acabó el tiempo! Le quitamos el VIP
                $user->suscripcion = false;
                $user->fecha_fin_suscripcion = null; // Limpiamos la fecha
                $user->save();
            }
        }

        // 4. Dejamos que la petición continúe su camino normal
        return $next($request);
    }
}