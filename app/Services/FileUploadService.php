<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Centralized File Upload Service
 *
 * All file uploads should go through this service to ensure:
 * - Consistent security validation
 * - Single source of truth for upload logic
 * - Easy maintenance and updates
 */
class FileUploadService
{
    /**
     * Allowed MIME types for file uploads
     * Only safe, non-executable file types allowed
     */
    protected array $allowedMimeTypes = [
        // Images
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
        // Documents
        'application/pdf',
        // Archives
        'application/zip',
        'application/x-zip-compressed',
    ];

    /**
     * Maximum file size in KB
     */
    protected int $maxFileSize = 10240; // 10MB (default, can be overridden)

    /**
     * Dangerous file extensions that should never be allowed
     */
    protected array $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'html', 'htm', 'js', 'jsx', 'exe', 'bat', 'cmd',
        'sh', 'bash', 'py', 'rb', 'pl', 'jar', 'war',
        'asp', 'aspx', 'jsp', 'jspx', 'cgi', 'bin',
    ];

    /**
     * Allowed file extensions (must match MIME types)
     */
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'zip'];

    /**
     * Validate uploaded file with comprehensive security checks
     */
    public function validateFile(UploadedFile $file, ?int $maxSize = null): array
    {
        $errors = [];
        $maxSize = $maxSize ?? $this->maxFileSize;

        // Check file size
        if ($file->getSize() > ($maxSize * 1024)) {
            $errors[] = "Dosya boyutu {$maxSize}KB'den büyük olamaz.";
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            $errors[] = 'Sadece JPEG, PNG, WebP, GIF, PDF ve ZIP dosyaları yüklenebilir.';
        }

        // Double-check MIME type using finfo (more reliable)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMimeType = finfo_file($finfo, $file->getPathname());
            finfo_close($finfo);

            if (! in_array($realMimeType, $this->allowedMimeTypes)) {
                $errors[] = 'Dosya türü güvenlik kontrolünden geçemedi.';
            }
        }

        // Check file extension - must match MIME type
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $this->allowedExtensions)) {
            $errors[] = 'Geçersiz dosya uzantısı.';
        }

        // Block dangerous extensions
        if (in_array($extension, $this->dangerousExtensions)) {
            $errors[] = 'Bu dosya türü güvenlik nedeniyle yüklenemez.';
        }

        // Check for malicious content (enhanced check)
        $content = file_get_contents($file->getPathname());
        $dangerousPatterns = [
            '<?php', '<?=', '<? ', '<script', 'javascript:', 'vbscript:',
            'onerror=', 'onload=', 'eval(', 'base64_decode',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                $errors[] = 'Dosya güvenlik riski içeriyor.';
                break;
            }
        }

        // Additional check: verify file is not executable
        if (function_exists('mime_content_type')) {
            $detectedMime = mime_content_type($file->getPathname());
            if ($detectedMime && strpos($detectedMime, 'executable') !== false) {
                $errors[] = 'Yürütülebilir dosyalar yüklenemez.';
            }
        }

        return $errors;
    }

    /**
     * Store file securely with UUID filename
     */
    public function storeFile(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        // Generate secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Store file with date-based directory structure
        $path = $file->storeAs($directory.'/'.date('Y/m'), $filename, $disk);

        return $path;
    }

    /**
     * Upload image with validation (backward compatibility with SystemHelper)
     */
    public function uploadImage(UploadedFile $file, string $folder = 'general', int $maxSize = 2048): array
    {
        try {
            // Validate file using secure validation
            $errors = $this->validateFile($file, $maxSize);
            if (! empty($errors)) {
                return [
                    'success' => false,
                    'message' => implode(' ', $errors),
                ];
            }

            // Store file securely
            $path = $this->storeFile($file, "{$folder}/images", 'public');

            // Generate URL
            $url = Storage::url($path);

            return [
                'success' => true,
                'filename' => basename($path),
                'path' => $path,
                'url' => asset($url),
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ];
        } catch (\Throwable $e) {
            \Log::error('File upload error', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'user_id' => optional(auth()->user())->id,
                'file_name' => $file->getClientOriginalName() ?? null,
            ]);
            
            if (function_exists('report')) {
                report($e);
            }
            
            return [
                'success' => false,
                'message' => 'Resim yüklenirken bir hata oluştu. Lütfen tekrar deneyin.',
            ];
        }
    }

    /**
     * Process uploaded files with security validation
     */
    public function processSecureUploads(array $files, ?int $maxSize = null): array
    {
        $processedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                $errors[] = 'Geçersiz dosya.';

                continue;
            }

            $validationErrors = $this->validateFile($file, $maxSize);
            if (! empty($validationErrors)) {
                $errors = array_merge($errors, $validationErrors);

                continue;
            }

            try {
                $path = $this->storeFile($file);
                $processedFiles[] = [
                    'file' => $file,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            } catch (\Throwable $e) {
                \Log::error('File upload error in batch', [
                    'message' => $e->getMessage(),
                    'exception' => $e,
                    'user_id' => optional(auth()->user())->id,
                    'file_name' => $file->getClientOriginalName() ?? null,
                ]);
                
                if (function_exists('report')) {
                    report($e);
                }
                
                $errors[] = 'Dosya yüklenirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        }

        return [
            'files' => $processedFiles,
            'errors' => $errors,
        ];
    }

    /**
     * Set maximum file size
     */
    public function setMaxFileSize(int $maxSize): self
    {
        $this->maxFileSize = $maxSize;

        return $this;
    }

    /**
     * Get allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Get allowed extensions
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }
}
