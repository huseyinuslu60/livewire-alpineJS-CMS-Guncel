<?php

namespace App\Observers;

use App\Support\Sanitizer;
use Modules\AgencyNews\Models\AgencyNews;

/**
 * AgencyNews Model Observer
 *
 * AgencyNews oluşturulurken ve güncellenirken content alanını sanitize eder.
 */
class AgencyNewsObserver
{
    /**
     * Handle the AgencyNews "creating" event.
     */
    public function creating(AgencyNews $agencyNews): void
    {
        if (isset($agencyNews->content)) {
            $agencyNews->content = Sanitizer::sanitizeHtml($agencyNews->content);
        }
    }

    /**
     * Handle the AgencyNews "updating" event.
     */
    public function updating(AgencyNews $agencyNews): void
    {
        if ($agencyNews->isDirty('content')) {
            $agencyNews->content = Sanitizer::sanitizeHtml($agencyNews->content);
        }
    }
}

