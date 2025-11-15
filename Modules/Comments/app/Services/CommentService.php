<?php

namespace Modules\Comments\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Comments\Models\Comment;

class CommentService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Comment::query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['statusFilter'])) {
            $query->ofStatus($filters['statusFilter']);
        }

        // Sıralama
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDirection = $filters['sortDirection'] ?? 'desc';

        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Yorum onayla
     *
     * @param  Comment  $comment  Comment modeli
     */
    public function approve(Comment $comment): Comment
    {
        return DB::transaction(function () use ($comment) {
            $comment->update(['status' => 'approved']);

            Log::info('Comment approved via CommentService', [
                'comment_id' => $comment->comment_id,
            ]);

            return $comment->fresh();
        });
    }

    /**
     * Yorum reddet
     *
     * @param  Comment  $comment  Comment modeli
     */
    public function reject(Comment $comment): Comment
    {
        return DB::transaction(function () use ($comment) {
            $comment->update(['status' => 'rejected']);

            Log::info('Comment rejected via CommentService', [
                'comment_id' => $comment->comment_id,
            ]);

            return $comment->fresh();
        });
    }

    /**
     * Yorum güncelle ve onayla
     *
     * @param  Comment  $comment  Comment modeli
     * @param  string  $newText  Yeni yorum metni
     */
    public function updateAndApprove(Comment $comment, string $newText): Comment
    {
        return DB::transaction(function () use ($comment, $newText) {
            $comment->update([
                'comment_text' => $newText,
                'status' => 'approved',
            ]);

            Log::info('Comment updated and approved via CommentService', [
                'comment_id' => $comment->comment_id,
            ]);

            return $comment->fresh();
        });
    }

    /**
     * Yorum güncelle
     *
     * @param  Comment  $comment  Comment modeli
     * @param  string  $newText  Yeni yorum metni
     */
    public function update(Comment $comment, string $newText): Comment
    {
        return DB::transaction(function () use ($comment, $newText) {
            $comment->update(['comment_text' => $newText]);

            Log::info('Comment updated via CommentService', [
                'comment_id' => $comment->comment_id,
            ]);

            return $comment->fresh();
        });
    }

    /**
     * Yorum sil
     *
     * @param  Comment  $comment  Comment modeli
     */
    public function delete(Comment $comment): void
    {
        DB::transaction(function () use ($comment) {
            $commentId = $comment->comment_id;
            $comment->delete();

            Log::info('Comment deleted via CommentService', [
                'comment_id' => $commentId,
            ]);
        });
    }
}
