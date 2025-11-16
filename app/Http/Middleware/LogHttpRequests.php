<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Logs\Models\UserLog;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sadece belirli route'ları logla
        if ($this->shouldLog($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Request'in loglanıp loglanmayacağını kontrol et
     */
    protected function shouldLog(Request $request): bool
    {
        // Admin paneli route'ları
        if ($request->is('admin/*') || $request->is('dashboard/*')) {
            return true;
        }

        // API route'ları
        if ($request->is('api/*')) {
            return true;
        }

        // Modül route'ları
        if ($request->is('posts/*') ||
            $request->is('articles/*') ||
            $request->is('categories/*') ||
            $request->is('users/*') ||
            $request->is('logs/*')) {
            return true;
        }

        return false;
    }

    /**
     * Request'i logla
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $action = $this->getActionFromRequest($request);

        UserLog::log(
            action: $action,
            description: "HTTP {$request->method()} {$request->path()} - Status: {$response->getStatusCode()}",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            url: $request->url(),
            method: $request->method(),
            metadata: [
                'status_code' => $response->getStatusCode(),
                'route_name' => $request->route()->getName(),
                'controller' => $request->route()->getActionName(),
                'parameters' => $request->route()->parameters(),
            ]
        );
    }

    /**
     * Request'ten action belirle
     */
    protected function getActionFromRequest(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // CRUD işlemlerini tespit et
        if (str_contains($path, '/create') || $method === 'POST') {
            return 'create';
        }

        if (str_contains($path, '/edit') || $method === 'PUT' || $method === 'PATCH') {
            return 'update';
        }

        if ($method === 'DELETE') {
            return 'delete';
        }

        if ($method === 'GET') {
            return 'view';
        }

        return 'request';
    }
}
