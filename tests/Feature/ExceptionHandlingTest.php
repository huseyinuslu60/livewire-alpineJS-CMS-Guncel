<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\Fixtures\TestToastComponent;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_exception_logs_detailed_error()
    {
        Log::fake();

        $component = Livewire::test(TestToastComponent::class);

        $component->call('triggerTestException');

        Log::assertLogged('error', function ($message, array $context) {
            return $message === 'Exception occurred' &&
                   $context['message'] === 'Test exception message' &&
                   isset($context['exception']) &&
                   isset($context['user_id']);
        });
    }

    public function test_handle_exception_shows_generic_message_to_user()
    {
        $component = Livewire::test(TestToastComponent::class);

        $component->call('triggerException');

        // Generic message should be shown, not the actual exception message
        $component->assertDispatched('show-error', 'İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.');
    }

    public function test_handle_exception_with_custom_user_message()
    {
        $component = Livewire::test(TestToastComponent::class);

        $component->call('triggerExceptionWithCustomMessage');

        $component->assertDispatched('show-error', 'Özel hata mesajı. Lütfen tekrar deneyin.');
    }

    public function test_handle_exception_includes_context_in_log()
    {
        Log::fake();

        $component = Livewire::test(TestToastComponent::class);

        $component->call('triggerExceptionWithContext');

        Log::assertLogged('error', function ($message, array $context) {
            return $message === 'Exception occurred' &&
                   isset($context['custom_key']) &&
                   $context['custom_key'] === 'custom_value' &&
                   isset($context['selected_ids']) &&
                   $context['selected_ids'] === [1, 2, 3];
        });
    }
}
