<?php

namespace App\Support;

class Sanitizer
{
    /**
     * Whitelist: p, a, strong, em, ul, ol, li, br, hr, blockquote, img, a href rel noopener
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Temel HTML etiketleri whitelist
        $allowedTags = '<p><a><strong><em><ul><ol><li><br><hr><blockquote><img><h1><h2><h3><h4><h5><h6><div><span><table><thead><tbody><tr><td><th>';

        // Etiketleri temizle ama whitelist'teki olanları bırak
        $sanitized = strip_tags($html, $allowedTags);

        // <a> etiketlerinden tehlikeli özellikleri kaldır ve noopener ekle
        $sanitized = preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            function ($matches) {
                $attrs = $matches[1];
                // javascript: ve data: protokollerini kaldır
                $attrs = preg_replace('/href\s*=\s*["\']?(javascript|data):/i', '', $attrs);
                // Eğer yoksa rel="noopener" ekle
                if (! preg_match('/rel\s*=/i', $attrs)) {
                    $attrs .= ' rel="noopener"';
                }

                return '<a '.$attrs.'>';
            },
            $sanitized
        );

        // <img> etiketlerinden tehlikeli özellikleri kaldır
        $sanitized = preg_replace('/<img\s+([^>]*?)>/i', '<img $1>', $sanitized);
        $sanitized = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $sanitized);

        return $sanitized;
    }

    /**
     * HTML'i tamamen escape et
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
