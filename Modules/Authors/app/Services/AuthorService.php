<?php

namespace Modules\Authors\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Authors\Models\Author;

class AuthorService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Author::with('user');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['statusFilter'])) {
            $query->ofStatus($filters['statusFilter']);
        }

        if (isset($filters['mainpageFilter']) && $filters['mainpageFilter'] !== '') {
            $query->where('show_on_mainpage', $filters['mainpageFilter']);
        }

        return $query
            ->orderBy('weight', 'asc')
            ->latest('created_at');
    }

    /**
     * Author oluştur
     *
     * @param  array<string, mixed>  $data  Author verileri
     */
    public function create(array $data): Author
    {
        return DB::transaction(function () use ($data) {
            $author = Author::create($data);

            Log::info('Author created via AuthorService', [
                'author_id' => $author->author_id,
                'title' => $author->title,
            ]);

            return $author;
        });
    }

    /**
     * Author güncelle
     *
     * @param  Author  $author  Author modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     */
    public function update(Author $author, array $data): Author
    {
        return DB::transaction(function () use ($author, $data) {
            $author->update($data);

            Log::info('Author updated via AuthorService', [
                'author_id' => $author->author_id,
                'title' => $author->title,
            ]);

            return $author->fresh();
        });
    }

    /**
     * Author sil
     *
     * @param  Author  $author  Author modeli
     */
    public function delete(Author $author): void
    {
        DB::transaction(function () use ($author) {
            $authorId = $author->author_id;
            $title = $author->title;

            $author->delete();

            Log::info('Author deleted via AuthorService', [
                'author_id' => $authorId,
                'title' => $title,
            ]);
        });
    }

    /**
     * Author durumunu toggle et
     *
     * @param  Author  $author  Author modeli
     * @return bool Yeni durum
     */
    public function toggleStatus(Author $author): bool
    {
        return DB::transaction(function () use ($author) {
            $newStatus = ! $author->status;
            $author->update(['status' => $newStatus]);

            Log::info('Author status toggled via AuthorService', [
                'author_id' => $author->author_id,
                'new_status' => $newStatus,
            ]);

            return $newStatus;
        });
    }

    /**
     * Ana sayfa durumunu toggle et
     *
     * @param  Author  $author  Author modeli
     * @return bool Yeni durum
     */
    public function toggleMainPage(Author $author): bool
    {
        return DB::transaction(function () use ($author) {
            $newValue = ! $author->show_on_mainpage;
            $author->update(['show_on_mainpage' => $newValue]);

            Log::info('Author main page status toggled via AuthorService', [
                'author_id' => $author->author_id,
                'show_on_mainpage' => $newValue,
            ]);

            return $newValue;
        });
    }
}
