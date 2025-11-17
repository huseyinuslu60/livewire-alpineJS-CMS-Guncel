<?php

namespace Modules\Lastminutes\Domain\Events;

use Modules\Lastminutes\Models\Lastminute;

class LastminuteUpdated
{
    public Lastminute $lastminute;
    public array $changedAttributes;

    public function __construct(Lastminute $lastminute, array $changedAttributes = [])
    {
        $this->lastminute = $lastminute;
        $this->changedAttributes = $changedAttributes;
    }
}

