<?php

namespace App\Observers;

use App\Support\Sanitizer;
use Modules\Posts\Models\File;

/**
 * Post File Model Observer
 *
 * Post File oluşturulurken ve güncellenirken title, alt_text ve caption alanlarını sanitize eder.
 * getClientOriginalName() XSS riskine karşı koruma sağlar.
 */
class PostFileObserver
{
    /**
     * Handle the File "creating" event.
     */
    public function creating(File $file): void
    {
        // Title (getClientOriginalName()'den geliyor) - XSS riski
        if (isset($file->title)) {
            $file->title = Sanitizer::escape($file->title);
        }

        // Alt text - XSS riski (HTML attribute'unda kullanılıyor)
        if (isset($file->alt_text)) {
            $file->alt_text = Sanitizer::escape($file->alt_text);
        }

        // Caption - XSS riski (Blade'de gösteriliyor)
        if (isset($file->caption)) {
            $file->caption = Sanitizer::escape($file->caption);
        }
    }

    /**
     * Handle the File "updating" event.
     */
    public function updating(File $file): void
    {
        if ($file->isDirty('title')) {
            $file->title = Sanitizer::escape($file->title);
        }

        if ($file->isDirty('alt_text')) {
            $file->alt_text = Sanitizer::escape($file->alt_text);
        }

        if ($file->isDirty('caption')) {
            $file->caption = Sanitizer::escape($file->caption);
        }
    }
}
