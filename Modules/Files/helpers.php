<?php

use Modules\Files\Helpers\FileHelper;

if (! function_exists('formatFileSize')) {
    function formatFileSize($bytes)
    {
        return FileHelper::formatFileSize($bytes);
    }
}

if (! function_exists('getFileIcon')) {
    function getFileIcon($mimeType)
    {
        return FileHelper::getFileIcon($mimeType);
    }
}

if (! function_exists('getFileColor')) {
    function getFileColor($mimeType)
    {
        return FileHelper::getFileColor($mimeType);
    }
}

if (! function_exists('getFileBadgeColor')) {
    function getFileBadgeColor($mimeType)
    {
        return FileHelper::getFileBadgeColor($mimeType);
    }
}
