<?php

namespace Tests\Feature;

use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_exception_logs_detailed_error()
    {
        Log::spy();

        $component = new class extends Component
        {
            use HandlesExceptionsWithToast;

            public function testMethod()
            {
                try {
                    throw new \RuntimeException('Test exception message');
                } catch (\Throwable $e) {
                    $this->handleException($e, 'Test user message');
                }
            }
        };

        $component->testMethod();

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Exception occurred', \Mockery::on(function ($context) {
                return isset($context['message']) &&
                       $context['message'] === 'Test exception message' &&
                       isset($context['exception']) &&
                       isset($context['user_id']);
            }));
    }

    public function test_handle_exception_shows_generic_message_to_user()
    {
        $component = Livewire::test(TestComponent::class);

        $component->call('triggerException');

        // Generic message should be shown, not the actual exception message
        $component->assertDispatched('toast', function ($event) {
            return $event['type'] === 'error' &&
                   $event['message'] === 'İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.';
        });
    }

    public function test_handle_exception_with_custom_user_message()
    {
        $component = Livewire::test(TestComponent::class);

        $component->call('triggerExceptionWithCustomMessage');

        $component->assertDispatched('toast', function ($event) {
            return $event['type'] === 'error' &&
                   $event['message'] === 'Özel hata mesajı. Lütfen tekrar deneyin.';
        });
    }

    public function test_handle_exception_includes_context_in_log()
    {
        Log::spy();

        $component = Livewire::test(TestComponent::class);

        $component->call('triggerExceptionWithContext');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Exception occurred', \Mockery::on(function ($context) {
                return isset($context['custom_key']) &&
                       $context['custom_key'] === 'custom_value' &&
                       isset($context['selected_ids']) &&
                       $context['selected_ids'] === [1, 2, 3];
            }));
    }
}

// Test component for Livewire testing
class TestComponent extends Component
{
    use HandlesExceptionsWithToast;

    public function triggerException()
    {
        try {
            throw new \RuntimeException('Sensitive error details that should not be shown');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function triggerExceptionWithCustomMessage()
    {
        try {
            throw new \RuntimeException('Sensitive error details');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Özel hata mesajı. Lütfen tekrar deneyin.');
        }
    }

    public function triggerExceptionWithContext()
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

