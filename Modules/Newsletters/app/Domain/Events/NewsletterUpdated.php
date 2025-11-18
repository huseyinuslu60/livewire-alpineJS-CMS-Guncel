<?php

namespace Modules\Newsletters\Domain\Events;

use Modules\Newsletters\Models\Newsletter;

class NewsletterUpdated
{
    public Newsletter $newsletter;

    public array $changedAttributes;

    public function __construct(Newsletter $newsletter, array $changedAttributes = [])
    {
        $this->newsletter = $newsletter;
        $this->changedAttributes = $changedAttributes;
    }
}
