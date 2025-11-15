<?php

namespace App\Traits;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;

/**
 * SecureFileUpload Trait
 *
 * @deprecated This trait delegates to FileUploadService for consistency
 * All upload logic is now centralized in App\Services\FileUploadService
 * This trait is kept for backward compatibility
 */
trait SecureFileUpload
{
    /**
     * Maximum file size in KB (can be overridden in classes using this trait)
     */
    protected int $maxFileSize = 10240; // 10MB (default)

    /**
     * Get FileUploadService instance
     */
    protected function getFileUploadService(): FileUploadService
    {
        return app(FileUploadService::class);
    }

    /**
     * Validate uploaded file
     *
     * @deprecated Use FileUploadService::validateFile() directly
     */
    protected function validateFile(UploadedFile $file): array
    {
        $maxSize = $this->maxFileSize ?? 10240;

        return $this->getFileUploadService()
            ->setMaxFileSize($maxSize)
            ->validateFile($file, $maxSize);
    }

    /**
     * Store file securely with UUID filename
     *
     * @deprecated Use FileUploadService::storeFile() directly
     */
    protected function storeFileSecurely(UploadedFile $file, string $directory = 'uploads'): string
    {
        return $this->getFileUploadService()->storeFile($file, $directory, 'public');
    }

    /**
     * Process uploaded files with security validation
     *
     * @deprecated Use FileUploadService::processSecureUploads() directly
     */
    protected function processSecureUploads(array $files): array
    {
        $maxSize = $this->maxFileSize ?? 10240;

        return $this->getFileUploadService()
            ->setMaxFileSize($maxSize)
            ->processSecureUploads($files, $maxSize);
    }
}
