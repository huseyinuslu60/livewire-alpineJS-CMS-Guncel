<?php

namespace Modules\Files\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Posts\Models\File as PostFile;

class ImageEditorService
{
    /**
     * Save edited image from image editor
     *
     * @param  UploadedFile  $image
     * @param  string|null  $fileId
     * @param  int|null  $index
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function saveEditedImage(UploadedFile $image, ?string $fileId = null, ?int $index = null): array
    {
        // Validate file
        if (! $image->isValid()) {
            throw new \InvalidArgumentException('Geçersiz dosya yüklendi');
        }

        // Generate storage path
        $storagePath = $this->generateStoragePath();
        $path = $image->store($storagePath, 'public');

        if (! $path) {
            throw new \RuntimeException('Dosya kaydedilemedi');
        }

        $imageUrl = asset('storage/'.$path);

        // Update Post File model if file_id provided
        if ($fileId) {
            $this->updatePostFile($fileId, $path, $image);
        }

        return [
            'image_url' => $imageUrl,
            'temp_path' => $path,
            'file_id' => $fileId,
            'index' => $index,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ];
    }

    /**
     * Generate storage path for uploaded images
     *
     * @return string
     */
    protected function generateStoragePath(): string
    {
        return 'posts/'.date('Y/m');
    }

    /**
     * Update Post File model with new image
     *
     * @param  string  $fileId
     * @param  string  $newPath
     * @param  UploadedFile  $image
     * @return void
     */
    protected function updatePostFile(string $fileId, string $newPath, UploadedFile $image): void
    {
        try {
            $file = PostFile::find($fileId);

            if (! $file) {
                Log::warning('Post file not found for update', [
                    'file_id' => $fileId,
                    'user_id' => auth()->id(),
                ]);

                return;
            }

            // Delete old file if exists
            $this->deleteOldFile($file->file_path);

            // Update file record
            $file->update([
                'file_path' => $newPath,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
            ]);

            Log::info('Post file updated successfully', [
                'file_id' => $fileId,
                'new_path' => $newPath,
                'user_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update post file', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            // Don't throw exception - file is already saved, just model update failed
            // This allows the operation to continue even if model update fails
        }
    }

    /**
     * Delete old file from storage
     *
     * @param  string  $oldPath
     * @return void
     */
    protected function deleteOldFile(string $oldPath): void
    {
        try {
            $fullPath = public_path('storage/'.$oldPath);

            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);

                Log::debug('Old file deleted', [
                    'path' => $oldPath,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete old file', [
                'path' => $oldPath,
                'error' => $e->getMessage(),
            ]);

            // Don't throw - file deletion failure shouldn't stop the operation
        }
    }
}

