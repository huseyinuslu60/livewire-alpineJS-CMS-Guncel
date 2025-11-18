<?php

namespace Modules\Newsletters\Domain\Events;

use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateUpdated
{
    public NewsletterTemplate $template;

    public array $changedAttributes;

    public function __construct(NewsletterTemplate $template, array $changedAttributes = [])
    {
        $this->template = $template;
        $this->changedAttributes = $changedAttributes;
    }
}
