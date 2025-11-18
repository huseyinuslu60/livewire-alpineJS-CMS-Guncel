<?php

namespace Modules\Posts\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Post Status Value Object
 *
 * Post durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli post durumları kabul edilir.
 */
final class PostStatus
{
    public const DRAFT = 'draft';

    public const PUBLISHED = 'published';

    public const SCHEDULED = 'scheduled';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PUBLISHED,
        self::SCHEDULED,
    ];

    private const STATUS_LABELS = [
        self::DRAFT => 'Pasif',
        self::PUBLISHED => 'Aktif',
        self::SCHEDULED => 'Zamanlanmış',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den PostStatus oluştur
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
     * Published durumu oluştur
     */
    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    /**
     * Scheduled durumu oluştur
     */
    public static function scheduled(): self
    {
        return new self(self::SCHEDULED);
    }

    /**
     * PostStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * PostStatus'u string olarak döndür (magic method)
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
     * İki PostStatus'u karşılaştır
     */
    public function equals(PostStatus $other): bool
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
     * Published durumu mu?
     */
    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    /**
     * Scheduled durumu mu?
     */
    public function isScheduled(): bool
    {
        return $this->value === self::SCHEDULED;
    }

    /**
     * Yayınlanmış veya zamanlanmış mı?
     */
    public function isPublic(): bool
    {
        return $this->isPublished() || $this->isScheduled();
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
     * Geçerli tüm durumları label'larıyla döndür (alias)
     */
    public static function labels(): array
    {
        return self::STATUS_LABELS;
    }

    /**
     * PostStatus validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz post durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}
