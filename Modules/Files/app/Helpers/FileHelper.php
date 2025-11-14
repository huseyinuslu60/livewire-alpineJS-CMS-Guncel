<?php

namespace Modules\Files\Helpers;

class FileHelper
{
    /**
     * Dosya boyutunu formatla
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2).' '.$sizes[(int) $i];
    }

    /**
     * Dosya ikonunu getir
     */
    public static function getFileIcon($mimeType)
    {
        if (str_contains($mimeType, 'image/')) {
            return 'fas fa-image';
        }
        if (str_contains($mimeType, 'video/')) {
            return 'fas fa-video';
        }
        if (str_contains($mimeType, 'audio/')) {
            return 'fas fa-music';
        }
        if (str_contains($mimeType, 'pdf')) {
            return 'fas fa-file-pdf';
        }
        if (str_contains($mimeType, 'word')) {
            return 'fas fa-file-word';
        }
        if (str_contains($mimeType, 'excel')) {
            return 'fas fa-file-excel';
        }
        if (str_contains($mimeType, 'powerpoint')) {
            return 'fas fa-file-powerpoint';
        }
        if (str_contains($mimeType, 'zip')) {
            return 'fas fa-file-archive';
        }

        return 'fas fa-file';
    }

    /**
     * Dosya rengini getir
     */
    public static function getFileColor($mimeType)
    {
        if (str_contains($mimeType, 'image/')) {
            return 'text-green-600';
        }
        if (str_contains($mimeType, 'video/')) {
            return 'text-purple-600';
        }
        if (str_contains($mimeType, 'audio/')) {
            return 'text-pink-600';
        }
        if (str_contains($mimeType, 'pdf')) {
            return 'text-red-600';
        }
        if (str_contains($mimeType, 'word')) {
            return 'text-blue-600';
        }
        if (str_contains($mimeType, 'excel')) {
            return 'text-green-600';
        }
        if (str_contains($mimeType, 'powerpoint')) {
            return 'text-orange-600';
        }

        return 'text-gray-600';
    }

    /**
     * Dosya badge rengini getir
     */
    public static function getFileBadgeColor($mimeType)
    {
        if (str_contains($mimeType, 'image/')) {
            return 'bg-green-100 text-green-800';
        }
        if (str_contains($mimeType, 'video/')) {
            return 'bg-purple-100 text-purple-800';
        }
        if (str_contains($mimeType, 'audio/')) {
            return 'bg-pink-100 text-pink-800';
        }
        if (str_contains($mimeType, 'pdf')) {
            return 'bg-red-100 text-red-800';
        }
        if (str_contains($mimeType, 'word')) {
            return 'bg-blue-100 text-blue-800';
        }
        if (str_contains($mimeType, 'excel')) {
            return 'bg-green-100 text-green-800';
        }
        if (str_contains($mimeType, 'powerpoint')) {
            return 'bg-orange-100 text-orange-800';
        }

        return 'bg-gray-100 text-gray-800';
    }
}
