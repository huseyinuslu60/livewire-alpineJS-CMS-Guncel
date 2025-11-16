<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Generate nonce for CSP
        $nonce = base64_encode(random_bytes(16));

        // Share nonce with views
        app()->instance('cspNonce', $nonce);
        view()->share('cspNonce', $nonce);

        // Content Security Policy
        if (app()->environment('local')) {
            // Local development CSP - unsafe-inline kullan ama nonce ile birlikte değil
            $csp = "default-src 'self'; ".
                   "img-src 'self' data: https: https://ui-avatars.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "script-src 'self' 'unsafe-eval' 'unsafe-inline' http://localhost:5173 http://127.0.0.1:5173 https://www.youtube.com https://www.youtube-nocookie.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "style-src 'self' 'unsafe-inline' http://localhost:5173 http://127.0.0.1:5173 https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; ".
                   "connect-src 'self' ws://localhost:5173 ws://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5173 https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "worker-src 'self' blob:; ".
                   'frame-src https://www.youtube.com https://www.youtube-nocookie.com;';
        } else {
            // Production CSP - Sadece nonce kullan
            $csp = "default-src 'self'; ".
                   "img-src 'self' data: https: https://ui-avatars.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "script-src 'self' 'nonce-{$nonce}' https://www.youtube.com https://www.youtube-nocookie.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; ".
                   "connect-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ".
                   "worker-src 'self' blob:; ".
                   'frame-src https://www.youtube.com https://www.youtube-nocookie.com;';
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
