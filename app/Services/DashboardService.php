<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Articles\Services\ArticleService;
use Modules\Logs\Services\LogService;
use Modules\Posts\Services\PostsService;
use Modules\Roles\Services\RoleService;
use Modules\User\Services\UserService;
use Spatie\Permission\Models\Role;

class DashboardService
{
    protected ArticleService $articleService;

    protected PostsService $postsService;

    protected UserService $userService;

    protected RoleService $roleService;

    protected LogService $logService;

    public function __construct(
        ?ArticleService $articleService = null,
        ?PostsService $postsService = null,
        ?UserService $userService = null,
        ?RoleService $roleService = null,
        ?LogService $logService = null
    ) {
        $this->articleService = $articleService ?? app(ArticleService::class);
        $this->postsService = $postsService ?? app(PostsService::class);
        $this->userService = $userService ?? app(UserService::class);
        $this->roleService = $roleService ?? app(RoleService::class);
        $this->logService = $logService ?? app(LogService::class);
    }

    /**
     * Get article statistics for user
     */
    public function getArticleStats(User $user, bool $canViewOwnArticlesOnly): array
    {
        $query = $this->articleService->getQuery();

        if ($canViewOwnArticlesOnly) {
            $query->where('author_id', $user->id);
        }

        return [
            'totalArticles' => (clone $query)->count(),
            'publishedArticles' => (clone $query)->where('status', 'published')->count(),
            'draftArticles' => (clone $query)->where('status', 'draft')->count(),
            'pendingArticles' => (clone $query)->where('status', 'pending')->count(),
            'recentArticles' => (clone $query)
                ->with(['author', 'category'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
            'thisMonthArticles' => (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'weeklyPopularArticles' => (clone $query)
                ->where('status', 'published')
                ->where('created_at', '>=', now()->subWeek())
                ->orderBy('hit', 'desc')
                ->limit(5)
                ->with(['author', 'category'])
                ->get(),
        ];
    }

    /**
     * Get weekly articles chart data
     */
    public function getWeeklyArticlesChart(User $user, bool $canViewOwnArticlesOnly)
    {
        $query = $this->articleService->getQuery()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7));

        if ($canViewOwnArticlesOnly) {
            $query->where('author_id', $user->id);
        }

        return $query
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get post statistics
     */
    public function getPostStats(): array
    {
        $query = $this->postsService->getQuery();

        return [
            'totalPosts' => (clone $query)->count(),
            'publishedPosts' => (clone $query)->ofStatus('published')->count(),
            'draftPosts' => (clone $query)->ofStatus('draft')->count(),
            'pendingPosts' => (clone $query)->ofStatus('pending')->count(),
            'recentPosts' => (clone $query)
                ->with(['author', 'primaryFile'])
                ->sortedLatest('created_at')
                ->limit(5)
                ->get(),
            'weeklyPopularPosts' => (clone $query)
                ->ofStatus('published')
                ->where('created_at', '>=', now()->subWeek())
                ->with(['author', 'primaryFile'])
                ->popular()
                ->limit(5)
                ->get(),
            'thisMonthPosts' => (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        $query = $this->userService->getQuery();

        return [
            'totalUsers' => (clone $query)->count(),
            'todayRegistrations' => (clone $query)->whereDate('created_at', today())->count(),
            'activeUsers' => (clone $query)->where('updated_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Get admin user statistics
     */
    public function getAdminUserStats(): array
    {
        $query = $this->userService->getQuery();

        return [
            'monthlyRegistrations' => (clone $query)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'thisWeekRegistrations' => (clone $query)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'thisMonthRegistrations' => (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'lastLoginUser' => (clone $query)
                ->with('roles')
                ->whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->first(),
            'mostActiveUsers' => (clone $query)
                ->with('roles')
                ->where('updated_at', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(),
            'weeklyRegistrations' => (clone $query)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }

    /**
     * Get role statistics
     */
    public function getRoleStats(): array
    {
        return [
            'roleDistribution' => Role::withCount('users')->get(),
            'totalRoles' => Role::count(),
        ];
    }

    /**
     * Get log statistics
     */
    public function getLogStats(): array
    {
        try {
            if (class_exists('\Modules\Logs\Models\Log')) {
                return [
                    'recentLogs' => $this->logService->getQuery()
                        ->with('user')
                        ->latest('created_at')
                        ->limit(5)
                        ->get(),
                ];
            }
        } catch (\Exception $e) {
            LogHelper::warning('LogService not available: '.$e->getMessage());
        }

        return [
            'recentLogs' => collect([]),
        ];
    }

    /**
     * Get query builder for articles
     */
    public function getArticleQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->articleService->getQuery();
    }

    /**
     * Get query builder for posts
     */
    public function getPostQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->postsService->getQuery();
    }

    /**
     * Get query builder for users
     */
    public function getUserQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->userService->getQuery();
    }
}
