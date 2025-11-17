<?php

namespace Modules\Files\Domain\Events;

use Modules\Files\Models\File;

class FileUpdated
{
    public File $file;
    public array $changedAttributes;

    public function __construct(File $file, array $changedAttributes = [])
    {
        $this->file = $file;
        $this->changedAttributes = $changedAttributes;
    }
}

