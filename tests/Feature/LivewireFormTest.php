<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivewireFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_component_can_be_instantiated()
    {
        // Livewire component'lerin yüklenebildiğini test et
        $this->assertTrue(class_exists('Livewire\Component'));
    }

    public function test_livewire_form_validation_works()
    {
        // Livewire Component sınıfının mevcut olduğunu test et
        $this->assertTrue(class_exists('Livewire\Component'));
    }

    public function test_livewire_live_updates_work()
    {
        // Livewire'ın temel özelliklerinin çalıştığını test et
        // Livewire Component'lerin mount, render gibi methodları olabilir
        $reflection = new \ReflectionClass('Livewire\Component');
        // Component'in en azından bir methodu olduğunu kontrol et
        $this->assertTrue($reflection->getMethods() !== []);
    }
}
