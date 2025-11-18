<?php

namespace Modules\Banks\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Banks\Models\InvestorQuestion;

class EloquentInvestorQuestionRepository implements InvestorQuestionRepositoryInterface
{
    public function findById(int $questionId): ?InvestorQuestion
    {
        return InvestorQuestion::find($questionId);
    }

    public function create(array $data): InvestorQuestion
    {
        return InvestorQuestion::create($data);
    }

    public function update(InvestorQuestion $question, array $data): InvestorQuestion
    {
        $question->update($data);

        return $question->fresh();
    }

    public function delete(InvestorQuestion $question): bool
    {
        return $question->delete();
    }

    public function findByIds(array $questionIds): \Illuminate\Database\Eloquent\Collection
    {
        return InvestorQuestion::whereIn('question_id', $questionIds)->get();
    }

    public function bulkUpdateStatus(array $questionIds, string $status): int
    {
        return InvestorQuestion::whereIn('question_id', $questionIds)->update(['status' => $status]);
    }

    public function getQuery(): Builder
    {
        return InvestorQuestion::query();
    }
}
