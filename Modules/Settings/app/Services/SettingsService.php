<?php

namespace Modules\Settings\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Settings\Models\SiteSetting;

class SettingsService
{
    /**
     * Tüm aktif ayarları grup bazlı olarak getir
     *
     * @return array<string, array<int, array{id: int, key: string, value: mixed, type: string, label: string, description: string, options: mixed, is_required: bool}>>
     */
    public function getAllGrouped(): array
    {
        /** @var Collection<int, SiteSetting> $settings */
        $settings = SiteSetting::active()->ordered()->get();

        /** @var Collection<string, Collection<int, SiteSetting>> $grouped */
        $grouped = $settings->groupBy('group');

        return $grouped->map(function ($group): array {
            /** @var Collection<int, SiteSetting> $group */
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

    /**
     * Belirli bir grubun ayarlarını getir
     *
     * @param  string  $group  Grup adı (general, seo, social, email, menu)
     * @return array<int, array{id: int, key: string, value: mixed, type: string, label: string, description: string, options: mixed, is_required: bool}>
     */
    public function getByGroup(string $group): array
    {
        $settings = SiteSetting::active()
            ->byGroup($group)
            ->ordered()
            ->get();

        return $settings->map(function ($setting) {
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
    }

    /**
     * Tek bir ayarı güncelle
     *
     * @param  int  $settingId  Ayar ID'si
     * @param  mixed  $value  Yeni değer
     * @param  \Illuminate\Http\UploadedFile|null  $file  Dosya yükleme (image type için)
     * @return SiteSetting
     * @throws \Exception
     */
    public function updateSetting(int $settingId, $value, $file = null): SiteSetting
    {
        return DB::transaction(function () use ($settingId, $value, $file) {
            $setting = SiteSetting::findOrFail($settingId);

            // File upload handling
            if ($setting->type === 'image' && $file) {
                // Eski dosyayı sil (varsa)
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                // Yeni dosyayı kaydet
                $value = $file->store('settings', 'public');
                Log::info('Setting image uploaded', [
                    'setting_id' => $settingId,
                    'key' => $setting->key,
                    'path' => $value,
                ]);
            }

            $setting->update(['value' => $value]);

            Log::info('Setting updated via SettingsService', [
                'setting_id' => $settingId,
                'key' => $setting->key,
                'type' => $setting->type,
            ]);

            return $setting->fresh();
        });
    }

    /**
     * Toplu ayar güncelleme
     *
     * @param  array<string, array<int, array{id: int, value: mixed}>>  $groupedSettings  Grup bazlı ayarlar
     * @return void
     * @throws \Exception
     */
    public function updateMultiple(array $groupedSettings): void
    {
        DB::transaction(function () use ($groupedSettings) {
            $updatedCount = 0;

            foreach ($groupedSettings as $group => $groupSettings) {
                foreach ($groupSettings as $setting) {
                    if (isset($setting['id']) && isset($setting['value'])) {
                        $siteSetting = SiteSetting::find($setting['id']);

                        if ($siteSetting) {
                            $value = $setting['value'];

                            // File upload handling
                            if ($siteSetting->type === 'image' && is_object($value)) {
                                // Eski dosyayı sil (varsa)
                                if ($siteSetting->value && Storage::disk('public')->exists($siteSetting->value)) {
                                    Storage::disk('public')->delete($siteSetting->value);
                                }

                                // Dosyayı storage'a kaydet
                                $value = $value->store('settings', 'public');
                                Log::info('Setting image uploaded in bulk update', [
                                    'setting_id' => $setting['id'],
                                    'key' => $siteSetting->key,
                                    'path' => $value,
                                ]);
                            }

                            $siteSetting->update(['value' => $value]);
                            $updatedCount++;

                            Log::debug('Setting updated in bulk', [
                                'id' => $setting['id'],
                                'key' => $siteSetting->key,
                                'group' => $group,
                            ]);
                        }
                    }
                }
            }

            Log::info('Multiple settings updated via SettingsService', [
                'updated_count' => $updatedCount,
                'groups' => array_keys($groupedSettings),
            ]);
        });
    }

    /**
     * Genel ayarları güncelle (general group)
     *
     * @param  array<string, mixed>  $data  Ayar verileri (key => value)
     * @return void
     * @throws \Exception
     */
    public function updateGeneral(array $data): void
    {
        $this->updateByGroup('general', $data);
    }

    /**
     * SEO ayarlarını güncelle
     *
     * @param  array<string, mixed>  $data  Ayar verileri
     * @return void
     * @throws \Exception
     */
    public function updateSeo(array $data): void
    {
        $this->updateByGroup('seo', $data);
    }

    /**
     * Sosyal medya ayarlarını güncelle
     *
     * @param  array<string, mixed>  $data  Ayar verileri
     * @return void
     * @throws \Exception
     */
    public function updateSocial(array $data): void
    {
        $this->updateByGroup('social', $data);
    }

    /**
     * E-posta ayarlarını güncelle
     *
     * @param  array<string, mixed>  $data  Ayar verileri
     * @return void
     * @throws \Exception
     */
    public function updateEmail(array $data): void
    {
        $this->updateByGroup('email', $data);

        // Kritik ayar değişikliği - log at
        Log::info('Email settings updated via SettingsService', [
            'keys' => array_keys($data),
        ]);
    }

    /**
     * Menü ayarlarını güncelle
     *
     * @param  array<string, mixed>  $data  Ayar verileri
     * @return void
     * @throws \Exception
     */
    public function updateMenu(array $data): void
    {
        $this->updateByGroup('menu', $data);
    }

    /**
     * Belirli bir grubun ayarlarını güncelle (internal helper)
     *
     * @param  string  $group  Grup adı
     * @param  array<string, mixed>  $data  Key-value çiftleri (key => value)
     * @return void
     * @throws \Exception
     */
    protected function updateByGroup(string $group, array $data): void
    {
        DB::transaction(function () use ($group, $data) {
            foreach ($data as $key => $value) {
                $setting = SiteSetting::where('key', $key)
                    ->byGroup($group)
                    ->first();

                if ($setting) {
                    $setting->update(['value' => $value]);

                    Log::debug('Setting updated by group', [
                        'group' => $group,
                        'key' => $key,
                    ]);
                }
            }

            Log::info('Settings updated by group via SettingsService', [
                'group' => $group,
                'updated_keys' => array_keys($data),
            ]);
        });
    }

    /**
     * Ayar değerini key ile getir (SiteSetting::getSetting() wrapper)
     *
     * @param  string  $key  Ayar anahtarı
     * @param  mixed  $default  Varsayılan değer
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return SiteSetting::getSetting($key, $default);
    }

    /**
     * Ayar değerini key ile set et (SiteSetting::setSetting() wrapper)
     *
     * @param  string  $key  Ayar anahtarı
     * @param  mixed  $value  Yeni değer
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        return DB::transaction(function () use ($key, $value) {
            $result = SiteSetting::setSetting($key, $value);

            if ($result) {
                Log::debug('Setting set via SettingsService', [
                    'key' => $key,
                ]);
            }

            return $result;
        });
    }

    /**
     * Tüm ayarları key-value array olarak getir
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return SiteSetting::getAllSettings();
    }
}

