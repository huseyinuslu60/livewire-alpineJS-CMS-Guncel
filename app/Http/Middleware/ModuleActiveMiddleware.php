<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        // Modülün aktif olup olmadığını kontrol et
        $module = Module::where('name', $moduleName)->first();

        if (! $module || ! $module->is_active) {
            // Modül pasif ise 404 döndür
            abort(404, 'Bu modül şu anda aktif değil.');
        }

        return $next($request);
    }
}
