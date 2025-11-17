<?php

namespace Modules\Files\Domain\Events;

use Modules\Files\Models\File;

class FileCreated
{
    public File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }
}

