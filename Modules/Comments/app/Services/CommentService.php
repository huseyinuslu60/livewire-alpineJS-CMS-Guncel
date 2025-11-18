<?php

namespace Modules\Comments\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Comments\Domain\Events\CommentApproved;
use Modules\Comments\Domain\Events\CommentCreated;
use Modules\Comments\Domain\Events\CommentDeleted;
use Modules\Comments\Domain\Events\CommentRejected;
use Modules\Comments\Domain\Events\CommentUpdated;
use Modules\Comments\Domain\Repositories\CommentRepositoryInterface;
use Modules\Comments\Domain\Services\CommentValidator;
use Modules\Comments\Models\Comment;

class CommentService
{
    protected CommentValidator $commentValidator;

    protected CommentRepositoryInterface $commentRepository;

    public function __construct(
        ?CommentValidator $commentValidator = null,
        ?CommentRepositoryInterface $commentRepository = null
    ) {
        $this->commentValidator = $commentValidator ?? app(CommentValidator::class);
        $this->commentRepository = $commentRepository ?? app(CommentRepositoryInterface::class);
    }

    /**
     * Create a new comment
     */
    public function create(array $data): Comment
    {
        try {
            // Validate comment data
            $this->commentValidator->validate($data);

            return DB::transaction(function () use ($data) {
                // Create via repository
                $comment = $this->commentRepository->create($data);

                // Fire domain event
                Event::dispatch(new CommentCreated($comment));

                LogHelper::info('Comment created', [
                    'comment_id' => $comment->comment_id,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment creation failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update a comment
     */
    public function update(Comment $comment, array $data): Comment
    {
        try {
            // Validate comment data
            $this->commentValidator->validate($data);

            return DB::transaction(function () use ($comment, $data) {
                // Update via repository
                $comment = $this->commentRepository->update($comment, $data);

                // Fire domain event
                Event::dispatch(new CommentUpdated($comment, array_keys($data)));

                LogHelper::info('Comment updated', [
                    'comment_id' => $comment->comment_id,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment update failed', [
                'comment_id' => $comment->comment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a comment
     */
    public function delete(Comment $comment): bool
    {
        try {
            return DB::transaction(function () use ($comment) {
                // Delete via repository
                $deleted = $this->commentRepository->delete($comment);

                if ($deleted) {
                    // Fire domain event
                    Event::dispatch(new CommentDeleted($comment));

                    LogHelper::info('Comment deleted', [
                        'comment_id' => $comment->comment_id,
                    ]);
                }

                return $deleted;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment deletion failed', [
                'comment_id' => $comment->comment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Approve a comment
     */
    public function approve(Comment $comment): Comment
    {
        try {
            return DB::transaction(function () use ($comment) {
                // Approve via repository
                $comment = $this->commentRepository->approve($comment);

                // Fire domain event
                Event::dispatch(new CommentApproved($comment));

                LogHelper::info('Comment approved', [
                    'comment_id' => $comment->comment_id,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment approval failed', [
                'comment_id' => $comment->comment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Reject a comment
     */
    public function reject(Comment $comment): Comment
    {
        try {
            return DB::transaction(function () use ($comment) {
                // Reject via repository
                $comment = $this->commentRepository->reject($comment);

                // Fire domain event
                Event::dispatch(new CommentRejected($comment));

                LogHelper::info('Comment rejected', [
                    'comment_id' => $comment->comment_id,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment rejection failed', [
                'comment_id' => $comment->comment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update comment text and approve
     */
    public function updateAndApprove(Comment $comment, string $commentText): Comment
    {
        try {
            // Validate comment text
            $this->commentValidator->validate(['comment_text' => $commentText]);

            return DB::transaction(function () use ($comment, $commentText) {
                // Update and approve
                $comment = $this->commentRepository->update($comment, [
                    'comment_text' => $commentText,
                    'status' => 'approved',
                ]);

                // Fire domain events
                Event::dispatch(new CommentUpdated($comment, ['comment_text', 'status']));
                Event::dispatch(new CommentApproved($comment));

                LogHelper::info('Comment updated and approved', [
                    'comment_id' => $comment->comment_id,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            LogHelper::error('Comment update and approval failed', [
                'comment_id' => $comment->comment_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Find a comment by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $commentId): Comment
    {
        $comment = $this->commentRepository->findById($commentId);

        if (! $comment) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Comment not found');
        }

        return $comment;
    }

    /**
     * Get query builder for comments
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Comments\Models\Comment>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->commentRepository->getQuery();
    }

    /**
     * Get comment statistics
     */
    public function getStatistics(): array
    {
        $query = $this->commentRepository->getQuery();

        return [
            'total' => $query->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }
}
