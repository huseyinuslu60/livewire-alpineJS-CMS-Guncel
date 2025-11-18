<?php

namespace Modules\Newsletters\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Newsletter Mail Status Value Object
 *
 * Newsletter mail durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli mail durumları kabul edilir.
 */
final class NewsletterMailStatus
{
    public const PENDING = 'pending';

    public const SENT = 'sent';

    public const FAILED = 'failed';

    private const VALID_STATUSES = [
        self::PENDING,
        self::SENT,
        self::FAILED,
    ];

    private const STATUS_LABELS = [
        self::PENDING => 'Beklemede',
        self::SENT => 'Gönderildi',
        self::FAILED => 'Başarısız',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den NewsletterMailStatus oluştur
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Pending durumu oluştur
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * Sent durumu oluştur
     */
    public static function sent(): self
    {
        return new self(self::SENT);
    }

    /**
     * Failed durumu oluştur
     */
    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    /**
     * NewsletterMailStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * NewsletterMailStatus'u string olarak döndür (magic method)
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
     * Badge class'ı döndür
     */
    public function getBadgeClass(): string
    {
        return match ($this->value) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::SENT => 'bg-green-100 text-green-800',
            self::FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * İki NewsletterMailStatus'u karşılaştır
     */
    public function equals(NewsletterMailStatus $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Pending durumu mu?
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Sent durumu mu?
     */
    public function isSent(): bool
    {
        return $this->value === self::SENT;
    }

    /**
     * Failed durumu mu?
     */
    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
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
     * NewsletterMailStatus validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz newsletter mail durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}
