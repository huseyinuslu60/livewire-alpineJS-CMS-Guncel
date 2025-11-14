<?php

namespace App\Policies;

use App\Models\User;

class FeaturedPolicy
{
    /**
     * Determine if the user can manage featured items
     */
    public function manageFeatured(User $user): bool
    {
        return $user->hasPermissionTo('view featured') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor');
    }
}
