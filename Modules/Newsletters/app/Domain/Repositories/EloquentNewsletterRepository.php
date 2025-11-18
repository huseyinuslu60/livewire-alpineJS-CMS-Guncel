<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\Newsletter;

class EloquentNewsletterRepository implements NewsletterRepositoryInterface
{
    public function findById(int $newsletterId): ?Newsletter
    {
        return Newsletter::find($newsletterId);
    }

    public function create(array $data): Newsletter
    {
        return Newsletter::create($data);
    }

    public function update(Newsletter $newsletter, array $data): Newsletter
    {
        $newsletter->update($data);

        return $newsletter->fresh();
    }

    public function delete(Newsletter $newsletter): bool
    {
        return $newsletter->delete();
    }
}
