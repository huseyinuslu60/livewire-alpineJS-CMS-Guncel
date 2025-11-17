<?php

namespace Modules\Categories\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Category Status Value Object
 * 
 * Category durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli category durumları kabul edilir.
 */
final class CategoryStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const DRAFT = 'draft';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::DRAFT,
    ];

    private const STATUS_LABELS = [
        self::ACTIVE => 'Aktif',
        self::INACTIVE => 'Pasif',
        self::DRAFT => 'Taslak',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den CategoryStatus oluştur
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Active durumu oluştur
     */
    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    /**
     * Inactive durumu oluştur
     */
    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    /**
     * Draft durumu oluştur
     */
    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    /**
     * CategoryStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * CategoryStatus'u string olarak döndür (magic method)
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
        return self::STATUS_LABELS[$this->value] ?? $this->value;
    }

    /**
     * İki CategoryStatus'u karşılaştır
     */
    public function equals(CategoryStatus $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Active durumu mu?
     */
    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    /**
     * Inactive durumu mu?
     */
    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    /**
     * Draft durumu mu?
     */
    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    /**
     * Geçerli tüm durumları döndür
     */
    public static function all(): array
    {
        return self::VALID_STATUSES;
    }

    /**
     * Geçerli tüm durumları label'larıyla döndür
     */
    public static function allWithLabels(): array
    {
        return self::STATUS_LABELS;
    }

    /**
     * CategoryStatus validation
     */
    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz category durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}

