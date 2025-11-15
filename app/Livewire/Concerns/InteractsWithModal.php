<?php

namespace App\Livewire\Concerns;

trait InteractsWithModal
{
    /**
     * Show confirmation modal
     *
     * @param string $title Modal title
     * @param string $message Modal message
     * @param string $action Livewire event name to dispatch on confirm
     * @param array $confirmPayload Payload to pass to the action event (e.g., ['id' => $id])
     * @param array $options Additional options (confirmLabel, cancelLabel, etc.)
     * @return void
     */
    protected function confirmModal(
        string $title,
        string $message,
        string $action,
        array $confirmPayload = [],
        array $options = []
    ): void {
        $payload = array_merge([
            'title' => $title,
            'message' => $message,
            'action' => $action,
        ], $options);

        // If confirmPayload has 'id', add it to payload (for backward compatibility)
        if (!empty($confirmPayload) && isset($confirmPayload['id'])) {
            $payload['id'] = $confirmPayload['id'];
        } elseif (!empty($confirmPayload)) {
            // If payload has other keys, merge them
            $payload = array_merge($payload, $confirmPayload);
        }

        $this->dispatch('modal:confirm', ...$payload);
    }
}

