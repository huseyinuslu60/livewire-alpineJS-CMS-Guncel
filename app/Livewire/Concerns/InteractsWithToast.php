<?php

namespace App\Livewire\Concerns;

trait InteractsWithToast
{
    /**
     * Dispatch a toast notification
     *
     * @param  string  $type  Toast type: 'success', 'error', 'warning', 'info'
     * @param  string  $message  Toast message
     * @param  array  $extra  Extra options (e.g., 'duration')
     * @param  bool  $persistOnRedirect  Redirect sonrası da gösterilsin mi? (session flash kullanır)
     */
    protected function toast(string $type, string $message, array $extra = [], bool $persistOnRedirect = false): void
    {
        $payload = array_merge([
            'type' => $type,
            'message' => $message,
        ], $extra);

        // Toast'ı event olarak gönder (anında gösterim için)
        $this->dispatch('toast', ...$payload);

        // Redirect durumunda toast'ın kaybolmaması için session flash'a da ekle
        // Bu sayede redirect sonrası da toast gösterilir
        if ($persistOnRedirect) {
            if ($type === 'success') {
                session()->flash('success', $message);
            } elseif ($type === 'error') {
                session()->flash('error', $message);
            } elseif ($type === 'warning' || $type === 'warn') {
                session()->flash('info', $message); // info olarak kaydet (layout'ta warn olarak gösteriliyor)
            } elseif ($type === 'info') {
                session()->flash('info', $message);
            }
        }
    }

    /**
     * Show success toast
     *
     * @param  string  $message  Toast message
     * @param  int  $duration  Duration in milliseconds (default: 5000)
     * @param  array  $extra  Extra options
     * @param  bool  $persistOnRedirect  Redirect sonrası da gösterilsin mi? (default: true)
     */
    protected function toastSuccess(string $message, int $duration = 5000, array $extra = [], bool $persistOnRedirect = true): void
    {
        $this->toast('success', $message, array_merge(['duration' => $duration], $extra), $persistOnRedirect);
    }

    /**
     * Show error toast
     *
     * @param  string  $message  Toast message
     * @param  int  $duration  Duration in milliseconds (default: 5000)
     * @param  array  $extra  Extra options
     * @param  bool  $persistOnRedirect  Redirect sonrası da gösterilsin mi? (default: true)
     */
    protected function toastError(string $message, int $duration = 5000, array $extra = [], bool $persistOnRedirect = true): void
    {
        $this->toast('error', $message, array_merge(['duration' => $duration], $extra), $persistOnRedirect);
    }

    /**
     * Show info toast
     *
     * @param  string  $message  Toast message
     * @param  int  $duration  Duration in milliseconds (default: 5000)
     * @param  array  $extra  Extra options
     * @param  bool  $persistOnRedirect  Redirect sonrası da gösterilsin mi? (default: false)
     */
    protected function toastInfo(string $message, int $duration = 5000, array $extra = [], bool $persistOnRedirect = false): void
    {
        $this->toast('info', $message, array_merge(['duration' => $duration], $extra), $persistOnRedirect);
    }

    /**
     * Show warning toast
     *
     * @param  string  $message  Toast message
     * @param  int  $duration  Duration in milliseconds (default: 5000)
     * @param  array  $extra  Extra options
     * @param  bool  $persistOnRedirect  Redirect sonrası da gösterilsin mi? (default: false)
     */
    protected function toastWarning(string $message, int $duration = 5000, array $extra = [], bool $persistOnRedirect = false): void
    {
        $this->toast('warning', $message, array_merge(['duration' => $duration], $extra), $persistOnRedirect);
    }
}
