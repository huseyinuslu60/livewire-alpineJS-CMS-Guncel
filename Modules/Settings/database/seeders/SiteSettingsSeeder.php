<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\SiteSetting;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Genel Ayarlar
            [
                'key' => 'site_title',
                'value' => 'Haber Sitesi',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Başlığı',
                'description' => 'Web sitesinin ana başlığı',
                'sort_order' => 1,
                'is_required' => true,
            ],
            [
                'key' => 'site_description',
                'value' => 'Güncel haberler ve son dakika gelişmeleri',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Site Açıklaması',
                'description' => 'Web sitesinin meta açıklaması',
                'sort_order' => 2,
                'is_required' => true,
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Site Logosu',
                'description' => 'Web sitesinin logosu',
                'sort_order' => 3,
                'is_required' => false,
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Site Favicon',
                'description' => 'Web sitesinin favicon\'u',
                'sort_order' => 4,
                'is_required' => false,
            ],
            [
                'key' => 'site_language',
                'value' => 'tr',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Site Dili',
                'description' => 'Web sitesinin varsayılan dili',
                'options' => [
                    ['value' => 'tr', 'label' => 'Türkçe'],
                    ['value' => 'en', 'label' => 'English'],
                ],
                'sort_order' => 5,
                'is_required' => true,
            ],
            [
                'key' => 'site_timezone',
                'value' => 'Europe/Istanbul',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Zaman Dilimi',
                'description' => 'Web sitesinin zaman dilimi',
                'sort_order' => 6,
                'is_required' => true,
            ],

            // SEO Ayarları
            [
                'key' => 'meta_keywords',
                'value' => 'haber, son dakika, güncel, haberler',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Meta Keywords',
                'description' => 'Arama motorları için anahtar kelimeler',
                'sort_order' => 1,
                'is_required' => false,
            ],
            [
                'key' => 'google_analytics',
                'value' => '',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Google Analytics ID',
                'description' => 'Google Analytics takip kodu',
                'sort_order' => 2,
                'is_required' => false,
            ],
            [
                'key' => 'google_search_console',
                'value' => '',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Google Search Console',
                'description' => 'Google Search Console doğrulama kodu',
                'sort_order' => 3,
                'is_required' => false,
            ],
            [
                'key' => 'robots_txt',
                'value' => 'User-agent: *\nAllow: /',
                'type' => 'textarea',
                'group' => 'seo',
                'label' => 'Robots.txt İçeriği',
                'description' => 'Arama motoru botları için yönergeler',
                'sort_order' => 4,
                'is_required' => false,
            ],

            // Sosyal Medya
            [
                'key' => 'facebook_url',
                'value' => '',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Facebook URL',
                'description' => 'Facebook sayfa linki',
                'sort_order' => 1,
                'is_required' => false,
            ],
            [
                'key' => 'twitter_url',
                'value' => '',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Twitter URL',
                'description' => 'Twitter profil linki',
                'sort_order' => 2,
                'is_required' => false,
            ],
            [
                'key' => 'instagram_url',
                'value' => '',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Instagram URL',
                'description' => 'Instagram profil linki',
                'sort_order' => 3,
                'is_required' => false,
            ],
            [
                'key' => 'youtube_url',
                'value' => '',
                'type' => 'text',
                'group' => 'social',
                'label' => 'YouTube URL',
                'description' => 'YouTube kanal linki',
                'sort_order' => 4,
                'is_required' => false,
            ],

            // E-posta Ayarları
            [
                'key' => 'mail_from_name',
                'value' => 'Haber Sitesi',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Gönderen Adı',
                'description' => 'E-posta gönderen adı',
                'sort_order' => 1,
                'is_required' => true,
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@habersitesi.com',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Gönderen E-posta',
                'description' => 'E-posta gönderen adresi',
                'sort_order' => 2,
                'is_required' => true,
            ],
            [
                'key' => 'mail_reply_to',
                'value' => 'info@habersitesi.com',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Yanıt Adresi',
                'description' => 'E-posta yanıt adresi',
                'sort_order' => 3,
                'is_required' => false,
            ],
            [
                'key' => 'mail_newsletter_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'label' => 'Bülten Aktif',
                'description' => 'E-posta bülteni gönderimini aktif et',
                'sort_order' => 4,
                'is_required' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
