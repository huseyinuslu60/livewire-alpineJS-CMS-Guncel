<?php

namespace Modules\Lastminutes\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Lastminute Status Value Object
 *
 * Lastminute durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli lastminute durumları kabul edilir.
 */
final class LastminuteStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const EXPIRED = 'expired';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::EXPIRED,
    ];

    private const STATUS_LABELS = [
        self::ACTIVE => 'Aktif',
        self::INACTIVE => 'Pasif',
        self::EXPIRED => 'Süresi Dolmuş',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den LastminuteStatus oluştur
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
     * Expired durumu oluştur
     */
    public static function expired(): self
    {
        return new self(self::EXPIRED);
    }

    /**
     * LastminuteStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * LastminuteStatus'u string olarak döndür (magic method)
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
     * İki LastminuteStatus'u karşılaştır
     */
    public function equals(LastminuteStatus $other): bool
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
     * Expired durumu mu?
     */
    public function isExpired(): bool
    {
        return $this->value === self::EXPIRED;
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
     * LastminuteStatus validation
     */
    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz lastminute durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}

