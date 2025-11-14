<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesRequestContext
{
    /**
     * Get request IP address safely (null-safe for console/queue contexts)
     */
    protected function getRequestIp(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        /** @var Request $request */
        $request = request();

        return $request->ip();
    }

    /**
     * Get request user agent safely (null-safe for console/queue contexts)
     */
    protected function getRequestUserAgent(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        /** @var Request $request */
        $request = request();

        return $request->userAgent();
    }

    /**
     * Get request URL safely (null-safe for console/queue contexts)
     */
    protected function getRequestUrl(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        /** @var Request $request */
        $request = request();

        return $request->url();
    }

    /**
     * Get request method safely (null-safe for console/queue contexts)
     */
    protected function getRequestMethod(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        /** @var Request $request */
        $request = request();

        return $request->method();
    }
}
