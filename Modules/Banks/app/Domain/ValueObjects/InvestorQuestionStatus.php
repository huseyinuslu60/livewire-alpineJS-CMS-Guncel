<?php

namespace Modules\Banks\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Investor Question Status Value Object
 * 
 * Investor question durumlarını type-safe olarak yönetir.
 * Business rule: Sadece geçerli question durumları kabul edilir.
 */
final class InvestorQuestionStatus
{
    public const PENDING = 'pending';
    public const ANSWERED = 'answered';
    public const REJECTED = 'rejected';

    private const VALID_STATUSES = [
        self::PENDING,
        self::ANSWERED,
        self::REJECTED,
    ];

    private const STATUS_LABELS = [
        self::PENDING => 'Beklemede',
        self::ANSWERED => 'Cevaplandı',
        self::REJECTED => 'Reddedildi',
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den InvestorQuestionStatus oluştur
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
     * Answered durumu oluştur
     */
    public static function answered(): self
    {
        return new self(self::ANSWERED);
    }

    /**
     * Rejected durumu oluştur
     */
    public static function rejected(): self
    {
        return new self(self::REJECTED);
    }

    /**
     * InvestorQuestionStatus'u string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * InvestorQuestionStatus'u string olarak döndür (magic method)
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
     * İki InvestorQuestionStatus'u karşılaştır
     */
    public function equals(InvestorQuestionStatus $other): bool
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
     * Answered durumu mu?
     */
    public function isAnswered(): bool
    {
        return $this->value === self::ANSWERED;
    }

    /**
     * Rejected durumu mu?
     */
    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
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
     * InvestorQuestionStatus validation
     */
    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Geçersiz investor question durumu: %s. Geçerli durumlar: %s', $value, implode(', ', self::VALID_STATUSES))
            );
        }
    }
}

