<?php

namespace Modules\Comments\Domain\ValueObjects;

use InvalidArgumentException;

final class CommentStatus
{
    public const APPROVED = 'approved';

    public const PENDING = 'pending';

    public const REJECTED = 'rejected';

    private string $value;

    private function __construct(string $value)
    {
        $this->ensureIsValidStatus($value);
        $this->value = $value;
    }

    public static function approved(): self
    {
        return new self(self::APPROVED);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function rejected(): self
    {
        return new self(self::REJECTED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }

    private function ensureIsValidStatus(string $value): void
    {
        $validStatuses = [self::APPROVED, self::PENDING, self::REJECTED];

        if (! in_array($value, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid comment status: {$value}. Valid statuses are: ".implode(', ', $validStatuses));
        }
    }
}
