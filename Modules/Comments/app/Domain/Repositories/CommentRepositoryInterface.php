<?php

namespace Modules\Comments\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Comments\Models\Comment;

interface CommentRepositoryInterface
{
    public function create(array $data): Comment;

    public function update(Comment $comment, array $data): Comment;

    public function delete(Comment $comment): bool;

    public function findById(int $id): ?Comment;

    public function approve(Comment $comment): Comment;

    public function reject(Comment $comment): Comment;

    public function getQuery(): Builder;
}
