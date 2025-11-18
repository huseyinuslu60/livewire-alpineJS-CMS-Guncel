<?php

namespace Modules\Settings\Livewire;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Settings\Models\SiteSetting;
use Modules\Settings\Services\SettingsService;

class SiteSettings extends Component
{
    use WithFileUploads;

    /** @var array<string, array<int, array{id: int, key: string, value: mixed, type: string, label: string, description: string, options: mixed, is_required: bool}>> */
    public array $settings = [];

    public string $activeTab = 'general';

    public bool $isLoading = false;

    protected SettingsService $settingsService;

    public function boot()
    {
        $this->settingsService = app(SettingsService::class);
    }

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
        try {
            // File upload handling
            $setting = $this->settingsService->getSettingById($settingId);
            if ($setting && $setting->type === 'image' && is_object($value)) {
                $value = $value->store('settings', 'public');
            }

            $this->settingsService->updateSetting($settingId, $value);

            $this->dispatch('setting-updated', [
                'key' => $setting !== null ? $setting->key : '',
                'value' => $value,
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->addError('settings', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('settings', 'Ayar güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function saveSettings()
    {
        $this->isLoading = true;

        try {
            LogHelper::info('Settings save başladı', ['settings' => $this->settings]);

            // File upload handling - önce dosyaları kaydet
            $settingsToUpdate = [];
            foreach ($this->settings as $group => $groupSettings) {
                foreach ($groupSettings as $setting) {
                    if (isset($setting['value']) && isset($setting['id'])) {
                        $siteSetting = $this->settingsService->getSettingById($setting['id']);
                        if ($siteSetting && $siteSetting->type === 'image' && is_object($setting['value'])) {
                            // Dosyayı storage'a kaydet
                            $setting['value'] = $setting['value']->store('settings', 'public');
                            LogHelper::info('Logo yüklendi', ['path' => $setting['value']]);
                        }
                        $settingsToUpdate[] = $setting;
                    }
                }
            }

            // Service ile toplu güncelleme (N+1 query önleme)
            $this->settingsService->updateSettings($settingsToUpdate);

            session()->flash('success', 'Ayarlar başarıyla kaydedildi.');
            $this->dispatch('settings-saved');
            LogHelper::info('Settings save tamamlandı');

            // Ayarları yeniden yükle ki güncel değerler görünsün
            $this->loadSettings();
        } catch (\InvalidArgumentException $e) {
            \App\Helpers\LogHelper::warning('Settings save validation hatası', ['error' => $e->getMessage()]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('Settings save hatası', ['error' => $e->getMessage()]);
            session()->flash('error', 'Ayarlar kaydedilirken bir hata oluştu: '.$e->getMessage());
        } finally {
            $this->isLoading = false;
            LogHelper::info('isLoading false yapıldı');
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
