<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait SecureFileUpload
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
    protected int $maxFileSize = 10240;

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
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): array
    {
        $errors = [];

        $configuredMaxKb = (int) config('files.max_size_kb', $this->maxFileSize);
        if ($configuredMaxKb <= 0) {
            $configuredMaxKb = $this->maxFileSize;
        }
        if ($file->getSize() > ($configuredMaxKb * 1024)) {
            $errors[] = "Dosya boyutu {$configuredMaxKb}KB'den büyük olamaz.";
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            $errors[] = 'Sadece JPEG, PNG, WebP, GIF, PDF ve ZIP dosyaları yüklenebilir.';
        }

        // Double-check MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        if (! in_array($realMimeType, $this->allowedMimeTypes)) {
            $errors[] = 'Dosya türü güvenlik kontrolünden geçemedi.';
        }

        // Check file extension - must match MIME type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'zip'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions)) {
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
    protected function storeFileSecurely(UploadedFile $file, string $directory = 'uploads'): string
    {
        // Generate secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Store file
        $path = $file->storeAs($directory.'/'.date('Y/m'), $filename, 'public');

        return $path;
    }

    /**
     * Process uploaded files with security validation
     */
    protected function processSecureUploads(array $files): array
    {
        $processedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                $errors[] = 'Geçersiz dosya.';

                continue;
            }

            $validationErrors = $this->validateFile($file);
            if (! empty($validationErrors)) {
                $errors = array_merge($errors, $validationErrors);

                continue;
            }

            try {
                $path = $this->storeFileSecurely($file);
                $processedFiles[] = [
                    'file' => $file,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            } catch (\Exception $e) {
                $errors[] = 'Dosya yüklenirken hata oluştu: '.$e->getMessage();
            }
        }

        return [
            'files' => $processedFiles,
            'errors' => $errors,
        ];
    }
}
