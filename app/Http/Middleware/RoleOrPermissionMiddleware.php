<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $guards = empty($guards) ? [null] : $guards;
        $user = auth()->user();

        foreach ($guards as $guard) {
            // Eğer guard bir role ise
            if ($user->hasRole($guard)) {
                return $next($request);
            }
            // Eğer guard bir permission ise
            if ($user->can($guard)) {
                return $next($request);
            }
        }

        abort(403, 'Bu işlem için yetkiniz veya rolünüz bulunmuyor.');
    }
}
