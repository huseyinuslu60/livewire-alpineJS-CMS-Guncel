<?php

namespace Modules\Newsletters\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Newsletter User Status Value Object
 *
 * Newsletter kullanıcı durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli kullanıcı durumları kabul edilir.
 */
final class NewsletterUserStatus
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    public const UNSUBSCRIBED = 'unsubscribed';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::UNSUBSCRIBED,
    ];

    private const STATUS_LABELS = [
        self::ACTIVE => 'Aktif',
        self::INACTIVE => 'Pasif',
        self::UNSUBSCRIBED => 'Abonelikten Çıktı',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den NewsletterUserStatus oluştur
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
     * Unsubscribed durumu oluştur
     */
    public static function unsubscribed(): self
    {
        return new self(self::UNSUBSCRIBED);
    }

    /**
     * NewsletterUserStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * NewsletterUserStatus'u string olarak döndür (magic method)
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
     * Tüm geçerli durumları döndür
     */
    public static function all(): array
    {
        return self::VALID_STATUSES;
    }

    /**
     * Tüm durumları label'larıyla döndür
     */
    public static function labels(): array
    {
        return self::STATUS_LABELS;
    }

    /**
     * Durumun geçerli olup olmadığını kontrol et
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::VALID_STATUSES, true);
    }

    /**
     * İki durumun eşit olup olmadığını kontrol et
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Durum değerini validate et
     */
    private function validate(string $value): void
    {
        if (! self::isValid($value)) {
            throw new InvalidArgumentException(
                "Geçersiz newsletter kullanıcı durumu: {$value}. Geçerli durumlar: ".implode(', ', self::VALID_STATUSES)
            );
        }
    }
}
