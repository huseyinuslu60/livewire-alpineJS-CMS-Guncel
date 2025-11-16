<?php

namespace Tests\Fixtures;

use App\Contracts\SupportsToastErrors;
use App\Traits\HandlesExceptionsWithToast;
use Livewire\Component;

class TestToastComponent extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast;

    public function toastError(string $message): void
    {
        // Dispatch show-error event for testing
        $this->dispatch('show-error', $message);
    }

    public function triggerTestException(): void
    {
        try {
            throw new \RuntimeException('Test exception message');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Test user message');
        }
    }

    public function triggerException(): void
    {
        try {
            throw new \RuntimeException('Sensitive error details that should not be shown');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function triggerExceptionWithCustomMessage(): void
    {
        try {
            throw new \RuntimeException('Sensitive error details');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Özel hata mesajı. Lütfen tekrar deneyin.');
        }
    }

    public function triggerExceptionWithContext(): void
    {
        try {
            throw new \RuntimeException('Test exception');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Test message', [
                'custom_key' => 'custom_value',
                'selected_ids' => [1, 2, 3],
            ]);
        }
    }

    public function render()
    {
        return '<div>Test Component</div>';
    }
}
