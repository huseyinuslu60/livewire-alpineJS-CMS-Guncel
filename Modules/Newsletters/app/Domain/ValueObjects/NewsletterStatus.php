<?php

namespace Modules\Newsletters\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Newsletter Status Value Object
 *
 * Newsletter durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli newsletter durumları kabul edilir.
 */
final class NewsletterStatus
{
    public const DRAFT = 'draft';

    public const SENDING = 'sending';

    public const SENT = 'sent';

    public const FAILED = 'failed';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::SENDING,
        self::SENT,
        self::FAILED,
    ];

    private const STATUS_LABELS = [
        self::DRAFT => 'Taslak',
        self::SENDING => 'Gönderiliyor',
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
     * String'den NewsletterStatus oluştur
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Draft durumu oluştur
     */
    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    /**
     * Sending durumu oluştur
     */
    public static function sending(): self
    {
        return new self(self::SENDING);
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
     * NewsletterStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * NewsletterStatus'u string olarak döndür (magic method)
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
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::SENDING => 'bg-blue-100 text-blue-800',
            self::SENT => 'bg-green-100 text-green-800',
            self::FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * İki NewsletterStatus'u karşılaştır
     */
    public function equals(NewsletterStatus $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Draft durumu mu?
     */
    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    /**
     * Sending durumu mu?
     */
    public function isSending(): bool
    {
        return $this->value === self::SENDING;
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
     * NewsletterStatus validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz newsletter durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}
