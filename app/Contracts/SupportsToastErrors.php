<?php

namespace App\Contracts;

interface SupportsToastErrors
{
    public function toastError(string $message): void;
}
