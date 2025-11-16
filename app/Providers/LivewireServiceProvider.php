<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Component;
use Livewire\Livewire;

class LivewireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Modül Livewire component'lerini kaydet
        $this->registerModuleComponents();
    }

    protected function registerModuleComponents()
    {
        // Not: Modül-specific Livewire component'leri artık kendi modül ServiceProvider'larında kayıtlı.
        // Bu dosya sadece herhangi bir modüle ait olmayan global component'leri içerir.

        // Modül-specific component'ler burada kayıtlı olmamalı.
        // Tüm modül component'leri kendi ServiceProvider'larında kayıtlı.
    }
}
