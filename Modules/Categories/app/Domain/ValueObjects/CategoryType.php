<?php

namespace Modules\Categories\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Category Type Value Object
 *
 * Category tiplerini type-safe olarak yönetir.
 * Business rule: Sadece geçerli category tipleri kabul edilir.
 */
final class CategoryType
{
    public const NEWS = 'news';

    public const GALLERY = 'gallery';

    public const VIDEO = 'video';

    private const VALID_TYPES = [
        self::NEWS,
        self::GALLERY,
        self::VIDEO,
    ];

    private const TYPE_LABELS = [
        self::NEWS => 'Haber',
        self::GALLERY => 'Galeri',
        self::VIDEO => 'Video',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den CategoryType oluştur
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * News tipi oluştur
     */
    public static function news(): self
    {
        return new self(self::NEWS);
    }

    /**
     * Gallery tipi oluştur
     */
    public static function gallery(): self
    {
        return new self(self::GALLERY);
    }

    /**
     * Video tipi oluştur
     */
    public static function video(): self
    {
        return new self(self::VIDEO);
    }

    /**
     * CategoryType'ı string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * CategoryType'ı string olarak döndür (magic method)
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Türkçe etiket döndür
     */
    public function getLabel(): string
    {
        return self::TYPE_LABELS[$this->value] ?? $this->value;
    }

    /**
     * İki CategoryType'ı karşılaştır
     */
    public function equals(CategoryType $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * News tipi mi?
     */
    public function isNews(): bool
    {
        return $this->value === self::NEWS;
    }

    /**
     * Gallery tipi mi?
     */
    public function isGallery(): bool
    {
        return $this->value === self::GALLERY;
    }

    /**
     * Video tipi mi?
     */
    public function isVideo(): bool
    {
        return $this->value === self::VIDEO;
    }

    /**
     * Geçerli tüm tipleri döndür
     */
    public static function all(): array
    {
        return self::VALID_TYPES;
    }

    /**
     * Geçerli tüm tipleri label'larıyla döndür
     */
    public static function allWithLabels(): array
    {
        return self::TYPE_LABELS;
    }

    /**
     * CategoryType validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz category tipi: %s. Geçerli tipler: %s', $value, implode(', ', self::VALID_TYPES))
            );
        }
    }
}
