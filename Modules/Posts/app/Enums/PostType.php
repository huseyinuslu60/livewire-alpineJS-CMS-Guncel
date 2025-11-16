<?php

namespace Modules\Posts\Enums;

enum PostType: string
{
    case News = 'news';
    case Gallery = 'gallery';
    case Video = 'video';

    /**
     * Get all type options as array for form selects
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::News->value => 'Haber',
            self::Gallery->value => 'Galeri',
            self::Video->value => 'Video',
        ];
    }

    /**
     * Get label for a type value
     *
     * @param string $value
     * @return string
     */
    public static function label(string $value): string
    {
        return self::options()[$value] ?? ucfirst($value);
    }

    /**
     * Get badge class for a type value
     *
     * @param string $value
     * @return string
     */
    public static function badgeClass(string $value): string
    {
        return match ($value) {
            self::News->value => 'bg-blue-100 text-blue-800',
            self::Gallery->value => 'bg-purple-100 text-purple-800',
            self::Video->value => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

