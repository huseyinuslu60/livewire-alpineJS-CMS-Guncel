<?php

namespace Modules\Files\Domain\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * ImageEditor Validator Domain Service
 *
 * ImageEditor iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Image file must be valid
 * - Image must be an image type
 * - Image size must not exceed limits
 */
class ImageEditorValidator
{
    /**
     * Image file validasyonu
     *
     * @param  UploadedFile  $image  Image file
     *
     * @throws InvalidArgumentException
     */
    public function validateImage(UploadedFile $image): void
    {
        if (! $image->isValid()) {
            throw new InvalidArgumentException('Geçersiz dosya yüklendi');
        }

        // Check if it's an image
        if (! str_starts_with($image->getMimeType(), 'image/')) {
            throw new InvalidArgumentException('Yüklenen dosya bir resim değil');
        }

        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($image->getSize() > $maxSize) {
            throw new InvalidArgumentException('Dosya boyutu çok büyük (maksimum 10MB)');
        }
    }
}
