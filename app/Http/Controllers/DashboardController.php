<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Services\AIContentSuggestionService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Modules\Articles\Services\ArticleService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    protected ArticleService $articleService;

    public function __construct(
        ?DashboardService $dashboardService = null,
        ?ArticleService $articleService = null
    ) {
        $this->dashboardService = $dashboardService ?? app(DashboardService::class);
        $this->articleService = $articleService ?? app(ArticleService::class);
    }

    public function index()
    {
        $user = Auth::user();

        // Tek bir dinamik dashboard - kullanıcının yetkilerine göre içerik göster
        return $this->dynamicDashboard($user);
    }

    private function dynamicDashboard($user)
    {
        $data = [];

        // Kullanıcının yetkilerine göre veri topla
        // Yazar kontrolü: Sadece kendi içeriklerini görebilme yetkisi var mı?
        $canViewOwnArticlesOnly = $user->can('view articles') && ! $user->can('view all articles');

        if ($user->can('view articles')) {
            $articleStats = $this->dashboardService->getArticleStats($user, $canViewOwnArticlesOnly);
            $data = array_merge($data, $articleStats);
        }

        // Haber yönetimi yetkisi varsa (Editör ve Admin için)
        if ($user->can('view posts')) {
            $postStats = $this->dashboardService->getPostStats();
            $data = array_merge($data, $postStats);
        }

        // Kullanıcı yönetimi yetkisi varsa
        if ($user->can('view users')) {
            $userStats = $this->dashboardService->getUserStats();
            $data = array_merge($data, $userStats);
        }

        // Rol yönetimi yetkisi varsa
        if ($user->can('view roles')) {
            $roleStats = $this->dashboardService->getRoleStats();
            $data = array_merge($data, $roleStats);
        }

        // Log görüntüleme yetkisi varsa
        if ($user->can('view logs')) {
            $logStats = $this->dashboardService->getLogStats();
            $data = array_merge($data, $logStats);
        }

        // AI İçerik Önerileri (İçerik yönetimi yetkisi olanlar için)
        if ($user->can('view posts') || $user->can('view articles')) {
            try {
                $aiService = new AIContentSuggestionService;
                $suggestions = $aiService->getContentSuggestions(5);

                // Eğer boş dönerse bile array olarak set et
                if (! empty($suggestions) && is_array($suggestions) && count($suggestions) > 0) {
                    $data['aiSuggestions'] = $suggestions;
                } else {
                    $data['aiSuggestions'] = [];
                }
            } catch (\Exception $e) {
                LogHelper::error('AI Suggestions Error for user '.$user->email.': '.$e->getMessage());
                // Hata durumunda boş array set et ki blade'de kontrol edilebilsin
                $data['aiSuggestions'] = [];
            }
        }

        // Admin'e özel ek istatistikler (Kullanıcı ve rol yönetimi yetkisi olanlar için)
        if ($user->can('view users') && $user->can('view roles')) {
            // Sistem performansı
            $data['systemStats'] = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2).' MB',
                'disk_free_space' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2).' GB',
            ];

            $adminUserStats = $this->dashboardService->getAdminUserStats();
            $data = array_merge($data, $adminUserStats);

            // Aktif kullanıcı yüzdesi
            $data['activeUserPercentage'] = $data['totalUsers'] > 0 ? round(($data['activeUsers'] / $data['totalUsers']) * 100) : 0;
        }

        // Haftalık grafik verisi
        if ($user->can('view articles')) {
            // Yazar kontrolü - sadece yazar rolüne sahip olanlar kendi makalelerini görür
            // Süper admin ve admin tüm makaleleri görür
            $isAuthorOnly = $user->hasRole('yazar') && ! $user->hasRole('super_admin') && ! $user->hasRole('admin');
            $data['weeklyArticles'] = $this->dashboardService->getWeeklyArticlesChart($user, $isAuthorOnly);
        }

        /** @var view-string $view */
        $view = 'dashboard';

        return view($view, compact('data', 'user'));
    }
}
