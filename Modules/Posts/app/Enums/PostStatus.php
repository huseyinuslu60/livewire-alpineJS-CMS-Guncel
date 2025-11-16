<?php

namespace Modules\Posts\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Archived = 'archived';

    /**
     * Get all status options as array for form selects
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Draft->value => 'Pasif',
            self::Published->value => 'Aktif',
            self::Scheduled->value => 'Zamanlanmış',
            self::Archived->value => 'Arşivlendi',
        ];
    }

    /**
     * Get label for a status value
     */
    public static function label(string $value): string
    {
        return self::options()[$value] ?? ucfirst($value);
    }

    /**
     * Get badge class for a status value
     */
    public static function badgeClass(string $value): string
    {
        return match ($value) {
            self::Draft->value => 'bg-yellow-100 text-yellow-800',
            self::Published->value => 'bg-green-100 text-green-800',
            self::Scheduled->value => 'bg-blue-100 text-blue-800',
            self::Archived->value => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
