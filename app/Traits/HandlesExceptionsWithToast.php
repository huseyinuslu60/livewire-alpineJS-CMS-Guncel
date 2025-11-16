<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

trait HandlesExceptionsWithToast
{
    /**
     * Handle exception with logging and user-friendly message
     *
     * @param  \Throwable  $e  The exception to handle
     * @param  string  $userMessage  User-friendly message to show (default: generic error message)
     * @param  array  $context  Additional context for logging (e.g., ['selected_ids' => [1,2,3]])
     */
    public function handleException(\Throwable $e, string $userMessage = 'İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', array $context = []): void
    {
        // Prepare log context
        $logContext = array_merge([
            'message' => $e->getMessage(),
            'exception' => $e,
            'user_id' => optional(auth()->user())->id,
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'component' => get_class($this),
        ], $context);

        // Log the exception with full details
        Log::error('Exception occurred', $logContext);

        // Report to Laravel's exception handler (for Sentry, Bugsnag, etc.)
        if (function_exists('report')) {
            report($e);
        }

        // Show user-friendly message if this is a Livewire component
        if ($this instanceof Component) {
            // Most components using this trait also use InteractsWithToast which provides toastError
            // Try toastError first, then fallback to dispatch, then addError
            if ($this instanceof \App\Contracts\SupportsToastErrors) {
                $this->toastError($userMessage);
            } else {
                // Fallback to dispatch if toastError is not available
                $this->dispatch('show-error', $userMessage);
            }
        }
    }
}
