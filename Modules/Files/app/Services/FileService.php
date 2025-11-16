<?php

namespace Modules\Files\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Files\Models\File;

class FileService
{
    /**
     * Create a new file
     */
    public function create(array $data, ?UploadedFile $uploadedFile = null, ?string $storagePath = 'files'): File
    {
        return DB::transaction(function () use ($data, $uploadedFile, $storagePath) {
            // Handle file upload if provided
            if ($uploadedFile) {
                $originalName = $uploadedFile->getClientOriginalName();
                $extension = $uploadedFile->getClientOriginalExtension();
                $fileName = Str::uuid().'.'.$extension;

                // Store file securely
                $path = $uploadedFile->storeAs($storagePath, $fileName, 'public');
                $data['file_path'] = str_replace('storage/', '', $path);
                $data['type'] = $uploadedFile->getMimeType();
                $data['title'] = $data['title'] ?? $originalName;
            }

            $file = File::create($data);

            Log::info('File created', [
                'file_id' => $file->file_id,
                'title' => $file->title,
                'file_path' => $file->file_path,
            ]);

            return $file;
        });
    }

    /**
     * Update an existing file
     */
    public function update(File $file, array $data, ?UploadedFile $uploadedFile = null): File
    {
        try {
            return DB::transaction(function () use ($file, $data, $uploadedFile) {
                // Handle file replacement if provided
                if ($uploadedFile) {
                    // Delete old file
                    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }

                    $originalName = $uploadedFile->getClientOriginalName();
                    $extension = $uploadedFile->getClientOriginalExtension();
                    $fileName = Str::uuid().'.'.$extension;

                    $path = $uploadedFile->storeAs('files', $fileName, 'public');
                    $data['file_path'] = str_replace('storage/', '', $path);
                    $data['type'] = $uploadedFile->getMimeType();
                }

                $file->update($data);

                Log::info('File updated', [
                    'file_id' => $file->file_id,
                    'title' => $file->title,
                ]);

                return $file;
            });
        } catch (\Exception $e) {
            Log::error('FileService update error:', [
                'file_id' => $file->file_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a file
     */
    public function delete(File $file): void
    {
        try {
            DB::transaction(function () use ($file) {
                // Delete physical file
                if ($file->file_path) {
                    $fullPath = public_path('storage/'.$file->file_path);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    // Also try storage disk
                    if (Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                }

                $file->delete();

                Log::info('File deleted', [
                    'file_id' => $file->file_id,
                    'title' => $file->title,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('FileService delete error:', [
                'file_id' => $file->file_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk delete files
     */
    public function bulkDelete(array $fileIds): int
    {
        try {
            return DB::transaction(function () use ($fileIds) {
                $files = File::whereIn('file_id', $fileIds)->get();
                $deletedCount = 0;

                foreach ($files as $file) {
                    $this->delete($file);
                    $deletedCount++;
                }

                Log::info('Files bulk deleted', [
                    'count' => $deletedCount,
                    'file_ids' => $fileIds,
                ]);

                return $deletedCount;
            });
        } catch (\Exception $e) {
            Log::error('FileService bulkDelete error:', [
                'file_ids' => $fileIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

