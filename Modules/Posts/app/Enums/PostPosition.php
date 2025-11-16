<?php

namespace Modules\Posts\Enums;

enum PostPosition: string
{
    case Normal = 'normal';
    case Manset = 'manşet';
    case Surmanset = 'sürmanşet';
    case OneCikanlar = 'öne çıkanlar';

    /**
     * Get all position options as array for form selects
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Normal->value => 'Normal',
            self::Manset->value => 'Manşet',
            self::Surmanset->value => 'Sürmanşet',
            self::OneCikanlar->value => 'Öne Çıkanlar',
        ];
    }

    /**
     * Get label for a position value
     */
    public static function label(string $value): string
    {
        return self::options()[$value] ?? ucfirst($value);
    }

    /**
     * Map position to zone name for FeaturedService
     */
    public static function toZone(string $value): ?string
    {
        return match ($value) {
            self::Manset->value => 'manset',
            self::Surmanset->value => 'surmanset',
            self::OneCikanlar->value => 'one_cikanlar',
            default => null,
        };
    }
}
