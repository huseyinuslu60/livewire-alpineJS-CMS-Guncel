<?php

namespace Modules\Settings\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Settings\Services\SettingsService;

class SiteSettings extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast, WithFileUploads;

    protected SettingsService $settingsService;

    /** @var array<string, array<int, array{id: int, key: string, value: mixed, type: string, label: string, description: string, options: mixed, is_required: bool}>> */
    public array $settings = [];

    public string $activeTab = 'general';

    public bool $isLoading = false;

    protected $rules = [
        'settings.*.value' => 'nullable|string|max:65535',
        'settings.*.value.*' => 'nullable|image|max:2048', // 2MB max for images
    ];

    public function boot(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function mount()
    {
        Gate::authorize('view settings');

        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->settings = $this->settingsService->getAllGrouped();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updateSetting($settingId, $value)
    {
        try {
            $file = null;
            if (is_object($value)) {
                $file = $value;
                $value = null; // Service içinde handle edilecek
            }

            $setting = $this->settingsService->updateSetting($settingId, $value, $file);

            $this->dispatch('setting-updated', [
                'key' => $setting->key,
                'value' => $setting->value,
            ]);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Ayar güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'setting_id' => $settingId,
            ]);
        }
    }

    public function saveSettings()
    {
        $this->isLoading = true;

        try {
            $this->settingsService->updateMultiple($this->settings);

            $this->toastSuccess('Ayarlar başarıyla kaydedildi.');
            $this->dispatch('settings-saved');

            // Ayarları yeniden yükle ki güncel değerler görünsün
            $this->loadSettings();
        } catch (\Throwable $e) {
            $this->handleException($e, 'Ayarlar kaydedilirken bir hata oluştu. Lütfen tekrar deneyin.');
        } finally {
            $this->isLoading = false;
        }
    }

    public function resetSettings()
    {
        $this->loadSettings();
        $this->toastInfo('Ayarlar sıfırlandı.');
    }

    public function render()
    {
        $groups = [
            'general' => 'Genel Ayarlar',
            'seo' => 'SEO Ayarları',
            'social' => 'Sosyal Medya',
            'email' => 'E-posta Ayarları',
            'menu' => 'Menü Yönetimi',
        ];

        /** @var view-string $view */
        $view = 'settings::livewire.site-settings';

        return view($view, compact('groups'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
