<?php

namespace Modules\Newsletters\Services;

use App\Helpers\LogHelper;
use Modules\Newsletters\Domain\Repositories\NewsletterLogRepositoryInterface;
use Modules\Newsletters\Models\NewsletterLog;

class NewsletterLogService
{
    protected NewsletterLogRepositoryInterface $newsletterLogRepository;

    public function __construct(
        ?NewsletterLogRepositoryInterface $newsletterLogRepository = null
    ) {
        $this->newsletterLogRepository = $newsletterLogRepository ?? app(NewsletterLogRepositoryInterface::class);
    }

    /**
     * Find a newsletter log by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $logId): NewsletterLog
    {
        $log = $this->newsletterLogRepository->findById($logId);

        if (! $log) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Newsletter log not found');
        }

        return $log;
    }

    /**
     * Delete a newsletter log
     */
    public function delete(int $logId): bool
    {
        try {
            $log = $this->findById($logId);

            $deleted = $this->newsletterLogRepository->delete($log);

            if ($deleted) {
                LogHelper::info('Newsletter log deleted', [
                    'log_id' => $logId,
                ]);
            }

            return $deleted;
        } catch (\Exception $e) {
            LogHelper::error('Newsletter log deletion failed', [
                'log_id' => $logId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get query builder for newsletter logs
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Newsletters\Models\NewsletterLog>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->newsletterLogRepository->getQuery();
    }
}
