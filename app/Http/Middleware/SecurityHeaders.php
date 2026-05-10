<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 🛡️ SECURITY-ALIGMENT (v112): Allow popups (like OneDrive Picker) to communicate with parent
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        
        return $response;
    }
}
