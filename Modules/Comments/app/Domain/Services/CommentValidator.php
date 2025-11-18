<?php

namespace Modules\Comments\Domain\Services;

use InvalidArgumentException;
use Modules\Comments\Domain\ValueObjects\CommentStatus;

class CommentValidator
{
    public function validate(array $data): void
    {
        if (isset($data['comment_text']) && empty(trim($data['comment_text']))) {
            throw new InvalidArgumentException('Comment text cannot be empty.');
        }

        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new InvalidArgumentException('Comment name cannot be empty.');
        }

        if (isset($data['email']) && ! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format.');
        }

        if (isset($data['status'])) {
            CommentStatus::fromString($data['status']);
        }

        // Add more specific validation rules here if needed
    }
}
