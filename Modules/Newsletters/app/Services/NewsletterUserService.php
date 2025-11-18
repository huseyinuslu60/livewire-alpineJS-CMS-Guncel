<?php

namespace Modules\Newsletters\Services;

use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Newsletters\Domain\Repositories\NewsletterUserRepositoryInterface;
use Modules\Newsletters\Domain\ValueObjects\NewsletterUserStatus;
use Modules\Newsletters\Models\NewsletterUser;

class NewsletterUserService
{
    protected NewsletterUserRepositoryInterface $userRepository;

    public function __construct(
        ?NewsletterUserRepositoryInterface $userRepository = null
    ) {
        $this->userRepository = $userRepository ?? app(NewsletterUserRepositoryInterface::class);
    }

    /**
     * Find newsletter user by ID
     */
    public function findById(int $userId): NewsletterUser
    {
        $user = $this->userRepository->findById($userId);
        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Newsletter user not found');
        }

        return $user;
    }

    /**
     * Create a new newsletter user
     */
    public function create(array $data): NewsletterUser
    {
        try {
            return DB::transaction(function () use ($data) {
                $user = $this->userRepository->create($data);

                LogHelper::info('Newsletter kullanıcısı oluşturuldu', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                ]);

                return $user;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterUserService create error', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing newsletter user
     */
    public function update(NewsletterUser $user, array $data): NewsletterUser
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                $user = $this->userRepository->update($user, $data);

                LogHelper::info('Newsletter kullanıcısı güncellendi', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                ]);

                return $user;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterUserService update error', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a newsletter user
     */
    public function delete(NewsletterUser $user): bool
    {
        try {
            return DB::transaction(function () use ($user) {
                $userId = $user->user_id;
                $email = $user->email;

                $result = $this->userRepository->delete($user);

                LogHelper::info('Newsletter kullanıcısı silindi', [
                    'user_id' => $userId,
                    'email' => $email,
                ]);

                return $result;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterUserService delete error', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(NewsletterUser $user): NewsletterUser
    {
        $currentStatus = NewsletterUserStatus::fromString($user->status);

        $newStatus = match ($currentStatus->toString()) {
            NewsletterUserStatus::ACTIVE => NewsletterUserStatus::inactive(),
            NewsletterUserStatus::INACTIVE => NewsletterUserStatus::active(),
            NewsletterUserStatus::UNSUBSCRIBED => NewsletterUserStatus::active(),
            default => NewsletterUserStatus::active()
        };

        return $this->update($user, ['status' => $newStatus->toString()]);
    }

    /**
     * Get query builder for newsletter users
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Newsletters\Models\NewsletterUser>
     */
    public function getQuery(): Builder
    {
        return $this->userRepository->getQuery();
    }
}
