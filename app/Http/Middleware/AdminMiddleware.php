<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->id_rol !== 1) {
            return response()->json([
                'mensaje' => 'Acceso denegado: Privilegios insuficientes',
            ], 403);
        }

        return $next($request);
    }
}
