<?php

namespace App\Support;

/**
 * HTML Sanitizer
 *
 * XSS saldırılarına karşı HTML içeriğini temizler.
 * Whitelist yaklaşımı kullanır - sadece izin verilen tag ve attribute'lar korunur.
 */
class Sanitizer
{
    /**
     * İzin verilen HTML tag'leri
     */
    protected const ALLOWED_TAGS = [
        'p', 'br', 'hr',
        'strong', 'em', 'b', 'i', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'img',
        'div', 'span',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
    ];

    /**
     * Tag bazlı izin verilen attribute'lar
     */
    protected const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class'],
        'table' => ['class', 'border', 'cellpadding', 'cellspacing'],
        'td' => ['class', 'colspan', 'rowspan'],
        'th' => ['class', 'colspan', 'rowspan'],
        'div' => ['class'],
        'span' => ['class'],
        'p' => ['class'],
        'h1' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
    ];

    /**
     * Tehlikeli protokoller (javascript:, data:, vb.)
     */
    protected const DANGEROUS_PROTOCOLS = [
        'javascript:',
        'data:',
        'vbscript:',
        'file:',
        'about:',
    ];

    /**
     * HTML içeriğini sanitize et
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Önce tehlikeli tag'leri tamamen kaldır (script, iframe, object, embed, vb.)
        $html = self::removeDangerousTags($html);

        // İzin verilen tag'leri koru, diğerlerini kaldır
        $allowedTagsString = '<'.implode('><', self::ALLOWED_TAGS).'>';
        $html = strip_tags($html, $allowedTagsString);

        // Attribute'ları temizle
        $html = self::sanitizeAttributes($html);

        // Tehlikeli protokolleri kaldır
        $html = self::removeDangerousProtocols($html);

        // Event handler'ları kaldır (onclick, onerror, vb.)
        $html = self::removeEventHandlers($html);

        // CSS injection'a karşı style attribute'larını kaldır
        $html = self::removeStyleAttributes($html);

        // Boş tag'leri temizle
        $html = self::cleanEmptyTags($html);

        // Tüm <a> tag'lerine rel="noopener" ekle (post-processing)
        $html = self::addNoopenerToLinks($html);

        return trim($html);
    }

    /**
     * Tehlikeli tag'leri kaldır
     */
    protected static function removeDangerousTags(string $html): string
    {
        $dangerousTags = [
            'script', 'iframe', 'object', 'embed', 'form', 'input', 'button',
            'select', 'textarea', 'meta', 'link', 'style', 'base', 'frame',
            'frameset', 'applet', 'marquee',
        ];

        foreach ($dangerousTags as $tag) {
            // Açılış ve kapanış tag'lerini kaldır
            $html = preg_replace('/<'.$tag.'[^>]*>.*?<\/'.$tag.'>/is', '', $html);
            // Self-closing tag'leri kaldır
            $html = preg_replace('/<'.$tag.'[^>]*\/?>/i', '', $html);
        }

        return $html;
    }

    /**
     * Attribute'ları temizle - sadece izin verilen attribute'ları koru
     */
    protected static function sanitizeAttributes(string $html): string
    {
        // Önce attribute'sız tag'leri koru
        $html = preg_replace_callback(
            '/<([a-z][a-z0-9]*)\s*>/i',
            function ($matches) {
                $tagName = strtolower($matches[1]);
                // İzin verilen tag ise koru
                if (in_array($tagName, self::ALLOWED_TAGS)) {
                    return '<'.$tagName.'>';
                }

                return '';
            },
            $html
        );

        // Sonra attribute'lu tag'leri işle
        return preg_replace_callback(
            '/<([a-z][a-z0-9]*)\s+([^>]*?)>/i',
            function ($matches) {
                $tagName = strtolower($matches[1]);
                $attributes = $matches[2];

                // İzin verilen tag değilse kaldır
                if (! in_array($tagName, self::ALLOWED_TAGS)) {
                    return '';
                }

                // Bu tag için izin verilen attribute'ları al
                $allowedAttrs = self::ALLOWED_ATTRIBUTES[$tagName] ?? [];

                if (empty($allowedAttrs)) {
                    // Attribute izni yoksa, sadece tag'i bırak
                    return '<'.$tagName.'>';
                }

                // Attribute'ları parse et
                $cleanAttrs = [];
                preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $attributes, $attrMatches, PREG_SET_ORDER);

                foreach ($attrMatches as $attrMatch) {
                    $attrName = strtolower($attrMatch[1]);
                    $attrValue = $attrMatch[2];

                    // Sadece izin verilen attribute'ları ekle
                    if (in_array($attrName, $allowedAttrs)) {
                        // Özel kontroller
                        if ($attrName === 'href' && $tagName === 'a') {
                            // href için özel kontrol yapılacak
                            $cleanAttrs[] = $attrName.'="'.htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8').'"';
                        } elseif ($attrName === 'src' && $tagName === 'img') {
                            // src için özel kontrol yapılacak
                            $cleanAttrs[] = $attrName.'="'.htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8').'"';
                        } elseif ($attrName === 'rel' && $tagName === 'a') {
                            // rel için noopener ekle
                            $relValue = $attrValue;
                            if (strpos($relValue, 'noopener') === false) {
                                $relValue = ($relValue ? $relValue.' ' : '').'noopener';
                            }
                            $cleanAttrs[] = $attrName.'="'.htmlspecialchars($relValue, ENT_QUOTES, 'UTF-8').'"';
                        } elseif ($attrName === 'target' && $tagName === 'a') {
                            // target="_blank" için rel="noopener" ekle
                            if ($attrValue === '_blank') {
                                $hasNoopener = false;
                                foreach ($cleanAttrs as $existingAttr) {
                                    if (strpos($existingAttr, 'rel=') === 0) {
                                        $hasNoopener = true;
                                        break;
                                    }
                                }
                                if (! $hasNoopener) {
                                    $cleanAttrs[] = 'rel="noopener"';
                                }
                            }
                            $cleanAttrs[] = $attrName.'="'.htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8').'"';
                        } else {
                            // Diğer attribute'lar için basit escape
                            $cleanAttrs[] = $attrName.'="'.htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8').'"';
                        }
                    }
                }

                // target="_blank" varsa ama rel yoksa, rel="noopener" ekle
                if ($tagName === 'a') {
                    $hasTarget = false;
                    $hasRel = false;
                    foreach ($cleanAttrs as $attr) {
                        if (strpos($attr, 'target=') === 0) {
                            $hasTarget = true;
                        }
                        if (strpos($attr, 'rel=') === 0) {
                            $hasRel = true;
                        }
                    }
                    if ($hasTarget && ! $hasRel) {
                        $cleanAttrs[] = 'rel="noopener"';
                    }
                }

                return '<'.$tagName.($cleanAttrs ? ' '.implode(' ', $cleanAttrs) : '').'>';
            },
            $html
        );
    }

    /**
     * Tehlikeli protokolleri kaldır (javascript:, data:, vb.)
     */
    protected static function removeDangerousProtocols(string $html): string
    {
        foreach (self::DANGEROUS_PROTOCOLS as $protocol) {
            // href ve src attribute'larındaki tehlikeli protokolleri kaldır
            $html = preg_replace(
                '/(href|src)\s*=\s*["\']?'.preg_quote($protocol, '/').'[^"\'\s>]*/i',
                '$1="#"',
                $html
            );
        }

        return $html;
    }

    /**
     * Event handler'ları kaldır (onclick, onerror, vb.)
     */
    protected static function removeEventHandlers(string $html): string
    {
        // on* attribute'larını kaldır
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $html);

        return $html;
    }

    /**
     * Style attribute'larını kaldır (CSS injection'a karşı)
     */
    protected static function removeStyleAttributes(string $html): string
    {
        $html = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*style\s*=\s*[^\s>]*/i', '', $html);

        return $html;
    }

    /**
     * Boş tag'leri temizle
     */
    protected static function cleanEmptyTags(string $html): string
    {
        // Boş tag'leri kaldır (sadece whitespace içeren)
        $html = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/i', '', $html);

        return $html;
    }

    /**
     * Tüm <a> tag'lerine rel="noopener" ekle
     */
    protected static function addNoopenerToLinks(string $html): string
    {
        if (empty($html) || ! str_contains($html, '<a')) {
            return $html;
        }

        // DOMDocument kullanarak linkleri işle
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // HTML yüklenirken uyarıları bastır
        libxml_use_internal_errors(true);

        // UTF-8 meta tag ekle ve body içine sar
        $htmlWithWrapper = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'.$html.'</body></html>';

        $dom->loadHTML($htmlWithWrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        // Tüm <a> tag'lerini bul
        $links = $dom->getElementsByTagName('a');

        foreach ($links as $link) {
            // Ensure it's a DOMElement
            if (! ($link instanceof \DOMElement)) {
                continue;
            }

            $rel = $link->getAttribute('rel');
            $relParts = array_filter(explode(' ', $rel));

            // noopener yoksa ekle
            if (! in_array('noopener', $relParts, true)) {
                $relParts[] = 'noopener';
                $link->setAttribute('rel', implode(' ', $relParts));
            }
        }

        // body içeriğini geri al
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return $html;
        }

        $innerHTML = '';
        foreach ($body->childNodes as $child) {
            $innerHTML .= $dom->saveHTML($child);
        }

        return $innerHTML;
    }

    /**
     * HTML'i tamamen escape et
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
