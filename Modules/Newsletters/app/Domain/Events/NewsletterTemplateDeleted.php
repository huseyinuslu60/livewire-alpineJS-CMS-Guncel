<?php

namespace Modules\Newsletters\Domain\Events;

use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateDeleted
{
    public NewsletterTemplate $template;

    public function __construct(NewsletterTemplate $template)
    {
        $this->template = $template;
    }
}
