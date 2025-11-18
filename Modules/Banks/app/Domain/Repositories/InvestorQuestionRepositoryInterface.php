<?php

namespace Modules\Banks\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Banks\Models\InvestorQuestion;

interface InvestorQuestionRepositoryInterface
{
    public function findById(int $questionId): ?InvestorQuestion;

    public function create(array $data): InvestorQuestion;

    public function update(InvestorQuestion $question, array $data): InvestorQuestion;

    public function delete(InvestorQuestion $question): bool;

    public function findByIds(array $questionIds): \Illuminate\Database\Eloquent\Collection;

    public function bulkUpdateStatus(array $questionIds, string $status): int;

    public function getQuery(): Builder;
}
