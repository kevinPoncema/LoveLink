<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log información de debug de autenticación
        Log::info('[DEBUG] DebugAuth middleware - Información de request:', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'user' => auth()->user() ? auth()->user()->toArray() : null,
            'session_id' => session()->getId(),
            'auth_guard' => auth()->getDefaultDriver(),
            'sanctum_token' => $request->bearerToken(),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
            'cookies' => $request->cookies->all(),
            'is_authenticated' => auth()->check(),
        ]);

        return $next($request);
    }
}
