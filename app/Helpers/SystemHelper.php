<?php

namespace App\Helpers;

use Carbon\Carbon;

class SystemHelper
{
    /**
     * Resim yükleme fonksiyonu
     *
     * @deprecated Use App\Services\FileUploadService::uploadImage() instead
     * This method is kept for backward compatibility but delegates to FileUploadService
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @param  int  $maxSize
     * @return array
     */
    public static function uploadImage($file, $folder = 'general', $maxSize = 2048)
    {
        $fileUploadService = app(\App\Services\FileUploadService::class);

        return $fileUploadService->uploadImage($file, $folder, $maxSize);
    }

    /**
     * Türkçe tarih formatı
     *
     * @param  string|\DateTime  $date
     * @param  string  $format
     * @return string
     */
    public static function turkishDate($date, $format = 'd.m.Y H:i')
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        $turkishMonths = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        $turkishDays = [
            'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar',
        ];

        $formatted = $date->format($format);

        // Ay adlarını değiştir
        foreach ($turkishMonths as $num => $turkish) {
            $formatted = str_replace($date->format('F'), $turkish, $formatted);
        }

        // Gün adlarını değiştir
        foreach ($turkishDays as $english => $turkish) {
            $formatted = str_replace($date->format('l'), $turkish, $formatted);
        }

        return $formatted;
    }

    /**
     * Türkçe tarih formatı (sadece tarih)
     *
     * @param  string|\DateTime  $date
     * @return string
     */
    public static function turkishDateOnly($date)
    {
        return self::turkishDate($date, 'd F Y');
    }

    /**
     * Türkçe tarih formatı (sadece saat)
     *
     * @param  string|\DateTime  $date
     * @return string
     */
    public static function turkishTimeOnly($date)
    {
        return self::turkishDate($date, 'H:i');
    }

    /**
     * Türkçe tarih formatı (tam format)
     *
     * @param  string|\DateTime  $date
     * @return string
     */
    public static function turkishDateTime($date)
    {
        return self::turkishDate($date, 'd F Y l H:i');
    }

    /**
     * Dosya boyutunu okunabilir formata çevir
     *
     * @param  int  $bytes
     * @return string
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Metni kısalt
     *
     * @param  string  $text
     * @param  int  $limit
     * @param  string  $suffix
     * @return string
     */
    public static function truncateText($text, $limit = 100, $suffix = '...')
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit).$suffix;
    }

    /**
     * Slug oluştur
     *
     * @param  string  $text
     * @return string
     */
    public static function createSlug($text)
    {
        $turkishChars = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'C', 'Ğ' => 'G', 'İ' => 'I', 'Ö' => 'O', 'Ş' => 'S', 'Ü' => 'U',
        ];

        $text = strtr($text, $turkishChars);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);

        return trim($text, '-');
    }

    /**
     * Rastgele renk oluştur
     *
     * @return string
     */
    public static function randomColor()
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
        ];

        return $colors[array_rand($colors)];
    }

    /**
     * Avatar URL oluştur
     *
     * @param  string  $name
     * @param  int  $size
     * @return string
     */
    public static function generateAvatar($name, $size = 100)
    {
        $initials = '';
        $words = explode(' ', $name);

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        $initials = substr($initials, 0, 2);
        $color = self::randomColor();

        return "https://ui-avatars.com/api/?name={$initials}&size={$size}&background={$color}&color=fff&bold=true";
    }

    /**
     * Durum badge'i oluştur
     *
     * @param  string  $status
     * @return string
     */
    public static function statusBadge($status)
    {
        $badges = [
            'active' => '<span class="badge badge-success">Aktif</span>',
            'inactive' => '<span class="badge badge-secondary">Pasif</span>',
            'pending' => '<span class="badge badge-warning">Beklemede</span>',
            'approved' => '<span class="badge badge-success">Onaylandı</span>',
            'rejected' => '<span class="badge badge-danger">Reddedildi</span>',
            'draft' => '<span class="badge badge-info">Taslak</span>',
            'published' => '<span class="badge badge-success">Yayınlandı</span>',
            'archived' => '<span class="badge badge-secondary">Arşivlendi</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">'.ucfirst($status).'</span>';
    }

    /**
     * Modülün aktif olup olmadığını kontrol et
     */
    public static function isModuleActive($moduleName)
    {
        return \App\Models\Module::where('name', $moduleName)->where('is_active', true)->exists();
    }

    /**
     * Aktif modülleri getir
     */
    public static function getActiveModules()
    {
        return \App\Models\Module::active()->orderBy('sort_order')->get();
    }

    /**
     * Modül bilgilerini getir
     */
    public static function getModuleInfo($moduleName)
    {
        return \App\Models\Module::where('name', $moduleName)->first();
    }
}
