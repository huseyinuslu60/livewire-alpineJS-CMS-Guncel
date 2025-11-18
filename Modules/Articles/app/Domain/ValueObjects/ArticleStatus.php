<?php

namespace Modules\Articles\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Article Status Value Object
 *
 * Article durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli article durumları kabul edilir.
 */
final class ArticleStatus
{
    public const DRAFT = 'draft';

    public const PUBLISHED = 'published';

    public const PENDING = 'pending';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PUBLISHED,
        self::PENDING,
    ];

    private const STATUS_LABELS = [
        self::DRAFT => 'Taslak',
        self::PUBLISHED => 'Yayında',
        self::PENDING => 'Beklemede',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den ArticleStatus oluştur
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
     * Pending durumu oluştur
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * ArticleStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * ArticleStatus'u string olarak döndür (magic method)
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
     * İki ArticleStatus'u karşılaştır
     */
    public function equals(ArticleStatus $other): bool
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
     * Pending durumu mu?
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Yayınlanmış mı?
     */
    public function isPublic(): bool
    {
        return $this->isPublished();
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
     * ArticleStatus validation
     */
    private function validate(string $value): void
    {
        if (! in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz article durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}
