<?php

namespace App\Contracts;

interface SupportsSelectionReset
{
    public function resetSelection(): void;
}
