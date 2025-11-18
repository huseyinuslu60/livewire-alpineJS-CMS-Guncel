<?php

namespace Modules\Newsletters\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Newsletters\Models\NewsletterUser;

class EloquentNewsletterUserRepository implements NewsletterUserRepositoryInterface
{
    public function findById(int $userId): ?NewsletterUser
    {
        return NewsletterUser::find($userId);
    }

    public function create(array $data): NewsletterUser
    {
        return NewsletterUser::create($data);
    }

    public function update(NewsletterUser $user, array $data): NewsletterUser
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(NewsletterUser $user): bool
    {
        return $user->delete();
    }

    public function getQuery(): Builder
    {
        return NewsletterUser::query();
    }
}
