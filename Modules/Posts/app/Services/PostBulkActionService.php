<?php

namespace Modules\Posts\Services;

use Illuminate\Support\Facades\DB;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;

class PostBulkActionService
{
    /**
     * Toplu işlem uygula
     *
     * @param  string  $action  İşlem tipi (delete, activate, deactivate, newsletter_add, newsletter_remove)
     * @param  array<int>  $ids  Post ID'leri
     * @return string Başarı mesajı
     *
     * @throws \InvalidArgumentException
     */
    public function applyBulkAction(string $action, array $ids): string
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Post ID\'leri boş olamaz.');
        }

        $selectedCount = count($ids);

        return DB::transaction(function () use ($action, $ids, $selectedCount) {
            $posts = Post::whereIn('post_id', $ids);

            switch ($action) {
                case 'delete':
                    $posts->delete();

                    return $selectedCount.' haber başarıyla silindi.';

                case 'activate':
                    $posts->update(['status' => PostStatus::Published]);

                    return $selectedCount.' haber aktif yapıldı.';

                case 'deactivate':
                    $posts->update(['status' => PostStatus::Draft]);

                    return $selectedCount.' haber pasif yapıldı.';

                case 'newsletter_add':
                    $posts->update(['in_newsletter' => true]);

                    return $selectedCount.' haber bültene eklendi.';

                case 'newsletter_remove':
                    $posts->update(['in_newsletter' => false]);

                    return $selectedCount.' haber bültenden çıkarıldı.';

                default:
                    throw new \InvalidArgumentException("Geçersiz bulk action: {$action}");
            }
        });
    }
}

