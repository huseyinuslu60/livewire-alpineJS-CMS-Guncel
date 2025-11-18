<?php

namespace Modules\Banks\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Modules\Banks\Domain\Repositories\InvestorQuestionRepositoryInterface;
use Modules\Banks\Domain\ValueObjects\InvestorQuestionStatus;
use Modules\Banks\Models\InvestorQuestion;

class InvestorQuestionService
{
    protected InvestorQuestionRepositoryInterface $questionRepository;

    public function __construct(
        ?InvestorQuestionRepositoryInterface $questionRepository = null
    ) {
        $this->questionRepository = $questionRepository ?? app(InvestorQuestionRepositoryInterface::class);
    }

    /**
     * Find investor question by ID
     */
    public function findById(int $questionId): InvestorQuestion
    {
        $question = $this->questionRepository->findById($questionId);
        if (! $question) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Investor question not found');
        }

        return $question;
    }

    /**
     * Create a new investor question
     */
    public function create(array $data): InvestorQuestion
    {
        try {
            return DB::transaction(function () use ($data) {
                $question = $this->questionRepository->create($data);

                LogHelper::info('Yatırımcı sorusu oluşturuldu', [
                    'question_id' => $question->question_id,
                    'title' => $question->title,
                ]);

                return $question;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService create error', [
                'title' => $data['title'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing investor question
     */
    public function update(InvestorQuestion $question, array $data): InvestorQuestion
    {
        try {
            return DB::transaction(function () use ($question, $data) {
                $question = $this->questionRepository->update($question, $data);

                LogHelper::info('Yatırımcı sorusu güncellendi', [
                    'question_id' => $question->question_id,
                    'title' => $question->title,
                ]);

                return $question;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService update error', [
                'question_id' => $question->question_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an investor question
     */
    public function delete(InvestorQuestion $question): bool
    {
        try {
            return DB::transaction(function () use ($question) {
                $questionId = $question->question_id;
                $title = $question->title;

                $result = $this->questionRepository->delete($question);

                LogHelper::info('Yatırımcı sorusu silindi', [
                    'question_id' => $questionId,
                    'title' => $title,
                ]);

                return $result;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService delete error', [
                'question_id' => $question->question_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark question as answered
     */
    public function markAsAnswered(InvestorQuestion $question, string $answer, ?string $answerTitle = null, ?int $userId = null): InvestorQuestion
    {
        try {
            return DB::transaction(function () use ($question, $answer, $answerTitle, $userId) {
                $data = [
                    'answer' => $answer,
                    'answer_title' => $answerTitle,
                    'status' => InvestorQuestionStatus::answered()->toString(),
                ];

                if ($userId) {
                    $data['updated_by'] = $userId;
                }

                $question = $this->questionRepository->update($question, $data);

                LogHelper::info('Yatırımcı sorusu cevaplandı', [
                    'question_id' => $question->question_id,
                    'title' => $question->title,
                ]);

                return $question;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService markAsAnswered error', [
                'question_id' => $question->question_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update answer
     */
    public function updateAnswer(InvestorQuestion $question, string $answer, ?string $answerTitle = null, ?int $userId = null): InvestorQuestion
    {
        try {
            return DB::transaction(function () use ($question, $answer, $answerTitle, $userId) {
                $data = [
                    'answer' => $answer,
                    'answer_title' => $answerTitle,
                ];

                if ($userId) {
                    $data['updated_by'] = $userId;
                }

                $question = $this->questionRepository->update($question, $data);

                LogHelper::info('Yatırımcı sorusu cevabı güncellendi', [
                    'question_id' => $question->question_id,
                    'title' => $question->title,
                ]);

                return $question;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService updateAnswer error', [
                'question_id' => $question->question_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark question as rejected
     */
    public function markAsRejected(InvestorQuestion $question, ?int $userId = null): InvestorQuestion
    {
        try {
            return DB::transaction(function () use ($question, $userId) {
                $data = [
                    'status' => InvestorQuestionStatus::rejected()->toString(),
                ];

                if ($userId) {
                    $data['updated_by'] = $userId;
                }

                $question = $this->questionRepository->update($question, $data);

                LogHelper::info('Yatırımcı sorusu reddedildi', [
                    'question_id' => $question->question_id,
                    'title' => $question->title,
                ]);

                return $question;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService markAsRejected error', [
                'question_id' => $question->question_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk delete questions
     */
    public function bulkDelete(array $questionIds): int
    {
        try {
            return DB::transaction(function () use ($questionIds) {
                $questions = $this->questionRepository->findByIds($questionIds);
                $count = 0;

                /** @var \Modules\Banks\Models\InvestorQuestion $question */
                foreach ($questions as $question) {
                    $this->questionRepository->delete($question);
                    $count++;
                }

                LogHelper::info('Yatırımcı soruları toplu silindi', [
                    'count' => $count,
                    'question_ids' => $questionIds,
                ]);

                return $count;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService bulkDelete error', [
                'question_ids' => $questionIds,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $questionIds, string $status): int
    {
        try {
            return DB::transaction(function () use ($questionIds, $status) {
                // Validate status
                InvestorQuestionStatus::fromString($status);

                $count = $this->questionRepository->bulkUpdateStatus($questionIds, $status);

                LogHelper::info('Yatırımcı soruları toplu durum güncellendi', [
                    'count' => $count,
                    'status' => $status,
                    'question_ids' => $questionIds,
                ]);

                return $count;
            });
        } catch (\Exception $e) {
            LogHelper::error('InvestorQuestionService bulkUpdateStatus error', [
                'question_ids' => $questionIds,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Increment hit counter
     */
    public function incrementHit(InvestorQuestion $question): InvestorQuestion
    {
        $question->increment('hit');

        return $question->fresh();
    }

    /**
     * Get query builder for investor questions
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Banks\Models\InvestorQuestion>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->questionRepository->getQuery();
    }
}
