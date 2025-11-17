<?php

namespace Modules\User\Domain\Events;

use App\Models\User;

class UserUpdated
{
    public User $user;
    public array $changedAttributes;

    public function __construct(User $user, array $changedAttributes = [])
    {
        $this->user = $user;
        $this->changedAttributes = $changedAttributes;
    }
}

