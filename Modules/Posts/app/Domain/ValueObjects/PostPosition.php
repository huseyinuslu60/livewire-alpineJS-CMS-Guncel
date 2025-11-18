<?php

namespace Modules\Posts\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Post Position Value Object
 *
 * Post pozisyonlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli post pozisyonları kabul edilir.
 */
final class PostPosition
{
    public const NORMAL = 'normal';

    public const MANSET = 'manşet';

    public const SURMANSET = 'sürmanşet';

    public const ONE_CIKANLAR = 'öne çıkanlar';

    private const VALID_POSITIONS = [
        self::NORMAL,
        self::MANSET,
        self::SURMANSET,
        self::ONE_CIKANLAR,
    ];

    private const POSITION_LABELS = [
        self::NORMAL => 'Normal',
        self::MANSET => 'Manşet',
        self::SURMANSET => 'Sürmanşet',
        self::ONE_CIKANLAR => 'Öne Çıkanlar',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den PostPosition oluştur
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Normal pozisyonu oluştur
     */
    public static function normal(): self
    {
        return new self(self::NORMAL);
    }

    /**
     * Manşet pozisyonu oluştur
     */
    public static function manset(): self
    {
        return new self(self::MANSET);
    }

    /**
     * Sürmanşet pozisyonu oluştur
     */
    public static function surmanset(): self
    {
        return new self(self::SURMANSET);
    }

    /**
     * Öne Çıkanlar pozisyonu oluştur
     */
    public static function oneCikanlar(): self
    {
        return new self(self::ONE_CIKANLAR);
    }

    /**
     * PostPosition'ı string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * PostPosition'ı string olarak döndür (magic method)
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
        return self::POSITION_LABELS[$this->value] ?? $this->value;
    }

    /**
     * İki PostPosition'ı karşılaştır
     */
    public function equals(PostPosition $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Normal pozisyon mu?
     */
    public function isNormal(): bool
    {
        return $this->value === self::NORMAL;
    }

    /**
     * Manşet pozisyon mu?
     */
    public function isManset(): bool
    {
        return $this->value === self::MANSET;
    }

    /**
     * Sürmanşet pozisyon mu?
     */
    public function isSurmanset(): bool
    {
        return $this->value === self::SURMANSET;
    }

    /**
     * Öne Çıkanlar pozisyon mu?
     */
    public function isOneCikanlar(): bool
    {
        return $this->value === self::ONE_CIKANLAR;
    }

    /**
     * Geçerli tüm pozisyonları döndür
     */
    public static function all(): array
    {
        return self::VALID_POSITIONS;
    }

    /**
     * Geçerli tüm pozisyonları label'larıyla döndür
     */
    public static function allWithLabels(): array
    {
        return self::POSITION_LABELS;
    }

    /**
     * Geçerli tüm pozisyonları label'larıyla döndür (alias)
     */
    public static function labels(): array
    {
        return self::POSITION_LABELS;
    }

    /**
     * PostPosition validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_POSITIONS, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz post pozisyonu: %s. Geçerli pozisyonlar: %s', $value, implode(', ', self::VALID_POSITIONS))
            );
        }
    }
}
