<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Production-safe logging helper
 * Debug log'ları sadece debug modunda çalışır
 */
class LogHelper
{
    /**
     * Debug log - sadece APP_DEBUG=true iken çalışır
     */
    public static function debug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug($message, $context);
        }
    }

    /**
     * Info log - sadece APP_DEBUG=true iken çalışır
     */
    public static function info(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::info($message, $context);
        }
    }

    /**
     * Warning log - her zaman çalışır (production'da da gerekli)
     */
    public static function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    /**
     * Error log - her zaman çalışır (production'da da gerekli)
     */
    public static function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }
}
