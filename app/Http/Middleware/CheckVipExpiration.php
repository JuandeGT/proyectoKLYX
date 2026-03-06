<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVipExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->suscripcion) {
            
            if ($user->fecha_fin_suscripcion && $user->fecha_fin_suscripcion < now()) {
                
                $user->suscripcion = false;
                $user->fecha_fin_suscripcion = null;
                $user->save();
            }
        }

        return $next($request);
    }
}