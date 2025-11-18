<?php

namespace Modules\Comments\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Comments\Models\Comment;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment->fresh();
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function findById(int $id): ?Comment
    {
        return Comment::find($id);
    }

    public function approve(Comment $comment): Comment
    {
        $comment->update(['status' => 'approved']);

        return $comment->fresh();
    }

    public function reject(Comment $comment): Comment
    {
        $comment->update(['status' => 'rejected']);

        return $comment->fresh();
    }

    public function getQuery(): Builder
    {
        return Comment::query();
    }
}
