<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\Newsletter;

interface NewsletterRepositoryInterface
{
    public function findById(int $newsletterId): ?Newsletter;

    public function create(array $data): Newsletter;

    public function update(Newsletter $newsletter, array $data): Newsletter;

    public function delete(Newsletter $newsletter): bool;
}
