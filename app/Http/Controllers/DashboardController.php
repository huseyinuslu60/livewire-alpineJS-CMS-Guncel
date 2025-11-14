<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AIContentSuggestionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Articles\Models\Article;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
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
            if ($canViewOwnArticlesOnly) {
                // Sadece kendi makalelerini görür (yazar gibi)
                $data['totalArticles'] = Article::where('author_id', $user->id)->count();
                $data['publishedArticles'] = Article::where('author_id', $user->id)->where('status', 'published')->count();
                $data['draftArticles'] = Article::where('author_id', $user->id)->where('status', 'draft')->count();
                $data['pendingArticles'] = Article::where('author_id', $user->id)->where('status', 'pending')->count();
                $data['recentArticles'] = Article::where('author_id', $user->id)
                    ->with(['author', 'category'])
                    ->latest('created_at')
                    ->limit(5)
                    ->get();
            } else {
                // Tüm makaleleri görür (editör/admin gibi)
                $data['totalArticles'] = Article::count();
                $data['publishedArticles'] = Article::where('status', 'published')->count();
                $data['draftArticles'] = Article::where('status', 'draft')->count();
                $data['pendingArticles'] = Article::where('status', 'pending')->count();
                $data['recentArticles'] = Article::with(['author', 'category'])
                    ->latest('created_at')
                    ->limit(5)
                    ->get();
            }

            $monthArticlesQuery = Article::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);

            if ($canViewOwnArticlesOnly) {
                $monthArticlesQuery->where('author_id', $user->id);
            }

            $data['thisMonthArticles'] = $monthArticlesQuery->count();

            // Haftanın en popüler makaleleri (hit sayısına göre)
            $weeklyPopularQuery = Article::where('status', 'published')
                ->where('created_at', '>=', now()->subWeek())
                ->orderBy('hit', 'desc')
                ->limit(5);

            if ($canViewOwnArticlesOnly) {
                $data['weeklyPopularArticles'] = $weeklyPopularQuery->where('author_id', $user->id)
                    ->with(['author', 'category'])
                    ->get();
            } else {
                $data['weeklyPopularArticles'] = $weeklyPopularQuery->with(['author', 'category'])->get();
            }
        }

        // Haber yönetimi yetkisi varsa (Editör ve Admin için)
        if ($user->can('view posts')) {
            $data['totalPosts'] = \Modules\Posts\Models\Post::count();
            $data['publishedPosts'] = \Modules\Posts\Models\Post::ofStatus('published')->count();
            $data['draftPosts'] = \Modules\Posts\Models\Post::ofStatus('draft')->count();
            $data['pendingPosts'] = \Modules\Posts\Models\Post::ofStatus('pending')->count();
            $data['recentPosts'] = \Modules\Posts\Models\Post::with(['author', 'primaryFile'])
                ->sortedLatest('created_at')
                ->limit(5)
                ->get();

            // Haftanın en popüler haberleri (view_count sayısına göre)
            $data['weeklyPopularPosts'] = \Modules\Posts\Models\Post::query()
                ->ofStatus('published')
                ->where('created_at', '>=', now()->subWeek())
                ->with(['author', 'primaryFile'])
                ->popular()
                ->limit(5)
                ->get();

            $data['thisMonthPosts'] = \Modules\Posts\Models\Post::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        // Kullanıcı yönetimi yetkisi varsa
        if ($user->can('view users')) {
            $data['totalUsers'] = User::count();
            $data['todayRegistrations'] = User::whereDate('created_at', today())->count();
            $data['activeUsers'] = User::where('updated_at', '>=', now()->subDays(30))->count();
        }

        // Rol yönetimi yetkisi varsa
        if ($user->can('view roles')) {
            $data['roleDistribution'] = Role::withCount('users')->get();
            $data['totalRoles'] = Role::count();
        }

        // Log görüntüleme yetkisi varsa
        if ($user->can('view logs')) {
            // Log modülü varsa kullan, yoksa boş array
            if (class_exists('\Modules\Logs\Models\Log')) {
                $data['recentLogs'] = \Modules\Logs\Models\Log::with('user')
                    ->latest()
                    ->limit(5)
                    ->get();
            } else {
                $data['recentLogs'] = collect([]);
            }
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
                \Log::error('AI Suggestions Error for user '.$user->email.': '.$e->getMessage());
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

            // Son 30 günlük kayıt trendi
            // Gerekçe: DATE() ve COUNT() sabit SQL fonksiyonları, parametre güvenliği sağlanmış
            $data['monthlyRegistrations'] = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Bu hafta kayıt olan kullanıcılar
            $data['thisWeekRegistrations'] = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

            // Bu ay kayıt olan kullanıcılar
            $data['thisMonthRegistrations'] = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

            // Aktif kullanıcı yüzdesi
            $data['activeUserPercentage'] = $data['totalUsers'] > 0 ? round(($data['activeUsers'] / $data['totalUsers']) * 100) : 0;

            // Son giriş yapan kullanıcı
            $data['lastLoginUser'] = User::with('roles')
                ->whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->first();

            // En aktif kullanıcılar (son 7 gün)
            $data['mostActiveUsers'] = User::with('roles')
                ->where('updated_at', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            // Son 7 günlük kayıt grafiği
            // Gerekçe: DATE() ve COUNT() sabit SQL fonksiyonları, parametre güvenliği sağlanmış
            $data['weeklyRegistrations'] = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        // Haftalık grafik verisi
        if ($user->can('view articles')) {
            // Gerekçe: DATE() ve COUNT() sabit SQL fonksiyonları, parametre güvenliği sağlanmış
            $weeklyArticlesQuery = Article::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(7));

            // Yazar kontrolü - sadece yazar rolüne sahip olanlar kendi makalelerini görür
            // Süper admin ve admin tüm makaleleri görür
            if ($user->hasRole('yazar') && ! $user->hasRole('super_admin') && ! $user->hasRole('admin')) {
                $weeklyArticlesQuery->where('author_id', $user->id);
            }

            $data['weeklyArticles'] = $weeklyArticlesQuery
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        /** @var view-string $view */
        $view = 'dashboard';

        return view($view, compact('data', 'user'));
    }
}
