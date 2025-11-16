<?php

namespace Modules\Comments\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Comments\Models\Comment;
use Modules\Logs\Models\UserLog;

class CommentObserver
{
    use HandlesRequestContext;

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        $changes = $comment->getChanges();
        $original = $comment->getOriginal();

        // Sadece önemli değişiklikleri logla
        if (isset($changes['status'])) {
            $oldStatus = $original['status'] ?? 'unknown';
            $newStatus = $changes['status'];

            $action = match ($newStatus) {
                'approved' => 'Yorum onaylandı',
                'rejected' => 'Yorum reddedildi',
                'pending' => 'Yorum beklemede durumuna alındı',
                default => 'Yorum durumu değiştirildi'
            };

            UserLog::log(
                action: 'update',
                description: "{$action}: {$comment->name} - ".\Str::limit($comment->comment_text, 50),
                modelType: Comment::class,
                modelId: $comment->comment_id,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $newStatus],
                ipAddress: $this->getRequestIp(),
                userAgent: $this->getRequestUserAgent(),
                url: $this->getRequestUrl(),
                method: $this->getRequestMethod()
            );
        } elseif (isset($changes['comment_text']) || isset($changes['name']) || isset($changes['email'])) {
            UserLog::log(
                action: 'update',
                description: "Yorum güncellendi: {$comment->name} - ".\Str::limit($comment->comment_text, 50),
                modelType: Comment::class,
                modelId: $comment->comment_id,
                oldValues: $original,
                newValues: $comment->toArray(),
                ipAddress: $this->getRequestIp(),
                userAgent: $this->getRequestUserAgent(),
                url: $this->getRequestUrl(),
                method: $this->getRequestMethod()
            );
        }
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        UserLog::log(
            action: 'delete',
            description: "Yorum silindi: {$comment->name} - ".\Str::limit($comment->comment_text, 50),
            modelType: Comment::class,
            modelId: $comment->comment_id,
            oldValues: $comment->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        UserLog::log(
            action: 'restore',
            description: "Yorum geri yüklendi: {$comment->name} - ".\Str::limit($comment->comment_text, 50),
            modelType: Comment::class,
            modelId: $comment->comment_id,
            newValues: $comment->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        UserLog::log(
            action: 'force_delete',
            description: "Yorum kalıcı olarak silindi: {$comment->name} - ".\Str::limit($comment->comment_text, 50),
            modelType: Comment::class,
            modelId: $comment->comment_id,
            oldValues: $comment->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }
}
