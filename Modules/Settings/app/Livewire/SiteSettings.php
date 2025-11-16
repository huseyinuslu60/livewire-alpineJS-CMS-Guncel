<?php

namespace Modules\Settings\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Settings\Models\SiteSetting;

class SiteSettings extends Component
{
    use WithFileUploads;

    /** @var array<string, array<int, array{id: int, key: string, value: mixed, type: string, label: string, description: string, options: mixed, is_required: bool}>> */
    public array $settings = [];

    public string $activeTab = 'general';

    public bool $isLoading = false;

    protected $rules = [
        'settings.*.value' => 'nullable|string|max:65535',
        'settings.*.value.*' => 'nullable|image|max:2048', // 2MB max for images
    ];

    public function mount()
    {
        Gate::authorize('view settings');

        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        /** @var \Illuminate\Support\Collection<int, \Modules\Settings\Models\SiteSetting> $settings */
        $settings = SiteSetting::active()->ordered()->get();

        /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \Modules\Settings\Models\SiteSetting>> $grouped */
        $grouped = $settings->groupBy('group');

        $this->settings = $grouped->map(function ($group): array {
            /** @var \Illuminate\Support\Collection<int, \Modules\Settings\Models\SiteSetting> $group */
            return $group->map(function ($setting): array {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'options' => $setting->options,
                    'is_required' => $setting->is_required,
                ];
            })->toArray();
        })->toArray();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updateSetting($settingId, $value)
    {
        $setting = SiteSetting::find($settingId);

        if (! $setting) {
            $this->addError('settings', 'Ayar bulunamadı.');

            return;
        }

        // File upload handling
        if ($setting->type === 'image' && is_object($value)) {
            $value = $value->store('settings', 'public');
        }

        $setting->update(['value' => $value]);

        $this->dispatch('setting-updated', [
            'key' => $setting->key,
            'value' => $value,
        ]);
    }

    public function saveSettings()
    {
        $this->isLoading = true;

        try {
            \Log::info('Settings save başladı', ['settings' => $this->settings]);

            foreach ($this->settings as $group => $groupSettings) {
                foreach ($groupSettings as $setting) {
                    if (isset($setting['value'])) {
                        $siteSetting = SiteSetting::find($setting['id']);
                        if ($siteSetting) {
                            $value = $setting['value'];

                            // File upload handling
                            if ($siteSetting->type === 'image' && is_object($value)) {
                                // Dosyayı storage'a kaydet
                                $value = $value->store('settings', 'public');
                                \Log::info('Logo yüklendi', ['path' => $value]);
                            }

                            $siteSetting->update(['value' => $value]);
                            \Log::info('Setting güncellendi', ['id' => $setting['id'], 'value' => $value]);
                        }
                    }
                }
            }

            session()->flash('success', 'Ayarlar başarıyla kaydedildi.');
            $this->dispatch('settings-saved');
            \Log::info('Settings save tamamlandı');

            // Ayarları yeniden yükle ki güncel değerler görünsün
            $this->loadSettings();
        } catch (\Exception $e) {
            \Log::error('Settings save hatası: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Ayarlar kaydedilirken bir hata oluştu: '.$e->getMessage());
        } finally {
            $this->isLoading = false;
            \Log::info('isLoading false yapıldı');
        }
    }

    public function resetSettings()
    {
        $this->loadSettings();
        session()->flash('info', 'Ayarlar sıfırlandı.');
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
