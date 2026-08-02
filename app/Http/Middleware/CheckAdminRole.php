<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->id_rol !== 1) {
            \Illuminate\Support\Facades\Log::warning("Intento de acceso no autorizado a módulo restringido por el usuario: " . ($request->user() ? $request->user()->Correo : 'Invitado'));
            return response()->json(['error' => 'Acceso restringido'], 403);
        }

        return $next($request);
    }
}
