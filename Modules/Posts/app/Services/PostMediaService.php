<?php

namespace Modules\Posts\Services;

use App\Support\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;

class PostMediaService
{
    /**
     * Store files for a post.
     */
    public function storeFiles(Post $post, array $files, ?string $type = null, ?int $primaryIndex = null, array $fileDescriptions = []): void
    {
        $uploadedFiles = [];
        $galleryData = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->storeFile($file, $post->post_id);

                $filename = $file->getClientOriginalName();
                $description = $fileDescriptions[$filename]['description'] ?? '';
                $altText = $fileDescriptions[$filename]['alt_text'] ?? '';

                // Sanitize: getClientOriginalName() XSS riskine karşı koruma
                $sanitizedTitle = Sanitizer::escape($file->getClientOriginalName());
                $sanitizedAltText = Sanitizer::escape($altText);
                $sanitizedCaption = Sanitizer::escape($description);

                $typeValue = $type instanceof PostType ? $type->value : ($type ?? PostType::News->value);
                $uploadedFiles[] = [
                    'post_id' => $post->post_id,
                    'title' => $sanitizedTitle,
                    'type' => $typeValue,
                    'file_path' => $path,
                    'primary' => $primaryIndex === $index,
                    'order' => $index,
                    'caption' => $sanitizedCaption,
                    'alt_text' => $sanitizedAltText,
                ];

            $galleryData[] = [
                'order' => $index,
                'filename' => $sanitizedTitle, // Sanitize edilmiş filename
                'file_path' => $path,
                'type' => $file->getMimeType(),
                'is_primary' => $primaryIndex === $index,
                'uploaded_at' => now()->toISOString(),
                'description' => $sanitizedCaption, // Sanitize edilmiş caption
                'alt_text' => $sanitizedAltText, // Sanitize edilmiş alt_text
            ];
            }
        }

        if (! empty($uploadedFiles)) {
            foreach ($uploadedFiles as &$file) {
                $file['primary'] = false;
            }

            $now = now();
            foreach ($uploadedFiles as &$file) {
                $file['created_at'] = $now;
                $file['updated_at'] = $now;
            }

            File::insert($uploadedFiles);

            $typeValue = $type instanceof PostType ? $type->value : ($type ?? PostType::News->value);
            if (! empty($galleryData) && $typeValue === PostType::Gallery->value) {
                $fileIndex = 0;
                foreach ($galleryData as &$data) {
                    if (isset($uploadedFiles[$fileIndex])) {
                        $data['file_path'] = $uploadedFiles[$fileIndex]['file_path'];
                    }
                    $fileIndex++;
                }

                $this->addFilesToGallery($post, $galleryData);
            }
        }
    }

    /**
     * Store a single file.
     */
    public function storeFile(UploadedFile $file, int $postId): string
    {
        $year = date('Y');
        $month = date('m');
        $path = "posts/{$year}/{$month}";

        return $file->store($path, 'public');
    }

    /**
     * Set primary file for a post.
     */
    public function setPrimaryFile(Post $post, int $primaryIndex): void
    {
        File::where('post_id', $post->post_id)->update(['primary' => false]);

        File::where('post_id', $post->post_id)
            ->skip($primaryIndex)
            ->take(1)
            ->update(['primary' => true]);
    }

    /**
     * Galeri içeriğini veritabanına kaydet
     */
    public function saveGalleryContent(Post $post, array $files, string|int|null $primaryFileId = null): bool
    {
        try {
            return DB::transaction(function () use ($post, $files, $primaryFileId) {
                $galleryData = $this->prepareGalleryData($files, $primaryFileId);
                $jsonContent = json_encode($galleryData, JSON_UNESCAPED_UNICODE);

                $result = DB::table('posts')
                    ->where('post_id', $post->post_id)
                    ->update(['content' => $jsonContent]);

                $post->content = $jsonContent;
                $post->save();
                $post->refresh();

                Log::info('Gallery content saved successfully', [
                    'post_id' => $post->post_id,
                    'files_count' => count($files),
                    'result' => $result,
                ]);

                return $result > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to save gallery content', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Galeri verilerini hazırla
     */
    public function prepareGalleryData(array $files, string|int|null $primaryFileId = null): array
    {
        $galleryData = [];

        foreach ($files as $index => $file) {
            $fileId = is_array($file) ? ($file['file_id'] ?? null) : null;

            if (empty($fileId)) {
                $filePath = $file['path'] ?? $file['file_path'] ?? '';
                $fileName = $file['original_name'] ?? $file['filename'] ?? '';

                if (! empty($filePath)) {
                    $fileId = 'existing_'.md5($filePath);
                } elseif (! empty($fileName)) {
                    $fileId = 'existing_'.md5($fileName);
                } else {
                    $fileId = 'existing_'.uniqid('', true);
                }
            }

            $isPrimary = false;
            if ($primaryFileId !== null) {
                $isPrimary = ((string) $fileId === (string) $primaryFileId);
            }

            $orderValue = $file['order'] ?? $index;

            // Sanitize filename, description ve alt_text (gallery meta'da XSS riski)
            $filename = $file['original_name'] ?? $file['filename'] ?? '';
            $description = $file['description'] ?? '';
            $altText = $file['alt_text'] ?? '';

            $galleryData[] = [
                'order' => $orderValue,
                'file_id' => $fileId,
                'filename' => Sanitizer::escape($filename),
                'file_path' => $file['path'] ?? $file['file_path'] ?? '',
                'type' => $file['type'] ?? 'image/jpeg',
                'is_primary' => $isPrimary,
                'uploaded_at' => $file['uploaded_at'] ?? now()->toISOString(),
                'description' => Sanitizer::escape($description),
                'alt_text' => Sanitizer::escape($altText),
            ];
        }

        return $galleryData;
    }

    /**
     * Dosyaları yeni sıralamaya göre yeniden düzenle
     */
    public function reorderFiles(array $files, array $order, bool $isIndexed = false): array
    {
        if ($isIndexed) {
            return $this->reorderIndexedFiles($files, $order);
        }

        return $this->reorderAssociativeFiles($files, $order);
    }

    /**
     * Index-based dosya array'ini yeniden sırala
     */
    protected function reorderIndexedFiles(array $files, array $order): array
    {
        if (empty($order)) {
            Log::warning('Invalid order data in reorderIndexedFiles', [
                'order' => $order,
                'files_count' => count($files),
            ]);

            return $files;
        }

        $reorderedFiles = [];

        foreach ($order as $fileId) {
            foreach ($files as $fileData) {
                if (($fileData['file_id'] ?? '') == $fileId) {
                    $reorderedFiles[] = $fileData;
                    break;
                }
            }
        }

        if (count($reorderedFiles) !== count($files)) {
            Log::warning('File count mismatch after reordering indexed files', [
                'original_count' => count($files),
                'reordered_count' => count($reorderedFiles),
            ]);

            return $files;
        }

        return $reorderedFiles;
    }

    /**
     * Associative dosya array'ini yeniden sırala
     */
    protected function reorderAssociativeFiles(array $files, array $order): array
    {
        if (empty($order)) {
            Log::warning('Invalid order data in reorderAssociativeFiles', [
                'order' => $order,
                'files_count' => count($files),
            ]);

            return $files;
        }

        $reorderedFiles = [];

        foreach ($order as $fileId) {
            if (isset($files[$fileId])) {
                $reorderedFiles[$fileId] = $files[$fileId];
            }
        }

        if (count($reorderedFiles) !== count($files)) {
            Log::warning('File count mismatch after reordering associative files', [
                'original_count' => count($files),
                'reordered_count' => count($reorderedFiles),
            ]);

            return $files;
        }

        return $reorderedFiles;
    }

    /**
     * Sıralama değişikliğini doğrula
     */
    public function validateOrder(array $order, array $files, bool $isIndexed = false): bool
    {
        if (empty($order)) {
            return false;
        }

        if (count($order) !== count($files)) {
            return false;
        }

        if ($isIndexed) {
            $fileIds = array_map(function ($file) {
                return $file['file_id'] ?? null;
            }, $files);

            foreach ($order as $fileId) {
                if (! in_array($fileId, $fileIds)) {
                    return false;
                }
            }
        } else {
            $fileKeys = array_keys($files);

            foreach ($order as $fileId) {
                if (! in_array($fileId, $fileKeys)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Galeriye yeni dosyalar ekle
     */
    public function addFilesToGallery(Post $post, array $galleryData): void
    {
        if (empty($galleryData)) {
            return;
        }

        foreach ($galleryData as &$data) {
            $data['is_primary'] = false;
        }

        $existingContentData = $post->content ? (is_string($post->content) ? json_decode($post->content, true) : $post->content) : [];

        if (! is_array($existingContentData)) {
            $existingContentData = [];
        }

        if (! empty($existingContentData)) {
            $existingContentData = array_filter($existingContentData, function ($item) {
                return ! empty($item['file_path']);
            });

            $updatedContentData = array_merge($existingContentData, $galleryData);
            $post->update(['content' => json_encode($updatedContentData, JSON_UNESCAPED_UNICODE)]);
        } else {
            $post->update(['content' => json_encode($galleryData, JSON_UNESCAPED_UNICODE)]);
        }

        if (config('app.debug')) {
            Log::info('PostMediaService added files to gallery:', [
                'post_id' => $post->post_id,
                'existing_count' => count($existingContentData),
                'new_count' => count($galleryData),
            ]);
        }
    }
}

