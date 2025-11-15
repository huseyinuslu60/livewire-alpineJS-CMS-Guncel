<?php

namespace Modules\Files\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Files\Models\File;

class FileService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = File::query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['mimeType'])) {
            if ($filters['mimeType'] === 'image') {
                $query->images();
            } else {
                $query->ofType($filters['mimeType']);
            }
        }

        return $query->sortedLatest('updated_at')->orderBy('file_id', 'desc');
    }

    /**
     * Dosya güncelle
     *
     * @param  File  $file  File modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @return File
     */
    public function update(File $file, array $data): File
    {
        return DB::transaction(function () use ($file, $data) {
            $file->update($data);

            Log::info('File updated via FileService', [
                'file_id' => $file->file_id,
                'title' => $file->title,
            ]);

            return $file->fresh();
        });
    }

    /**
     * Dosya sil
     *
     * @param  File  $file  File modeli
     * @return void
     */
    public function delete(File $file): void
    {
        DB::transaction(function () use ($file) {
            $fileId = $file->file_id;
            $filePath = $file->file_path;

            // Fiziksel dosyayı sil
            $fullPath = public_path('storage/'.$filePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $file->delete();

            Log::info('File deleted via FileService', [
                'file_id' => $fileId,
                'file_path' => $filePath,
            ]);
        });
    }

    /**
     * Toplu dosya sil
     *
     * @param  array<int>  $ids  File ID'leri
     * @return int Silinen dosya sayısı
     */
    public function deleteMultiple(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $files = File::whereIn('file_id', $ids)->get();
            $count = 0;

            foreach ($files as $file) {
                $this->delete($file);
                $count++;
            }

            Log::info('Multiple files deleted via FileService', [
                'count' => $count,
            ]);

            return $count;
        });
    }
}

