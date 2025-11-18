<?php

namespace Modules\Newsletters\Domain\Events;

use Modules\Newsletters\Models\Newsletter;

class NewsletterDeleted
{
    public Newsletter $newsletter;

    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }
}
