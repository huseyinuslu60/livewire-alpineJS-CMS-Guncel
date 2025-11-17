<?php

namespace Modules\Lastminutes\Domain\Events;

use Modules\Lastminutes\Models\Lastminute;

class LastminuteDeleted
{
    public Lastminute $lastminute;

    public function __construct(Lastminute $lastminute)
    {
        $this->lastminute = $lastminute;
    }
}

