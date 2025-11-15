<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Articles\Models\Article;
use Modules\Posts\Models\Post;

/**
 * Content Suggestion Service
 *
 * Generates content suggestions based on popular posts, articles, and trends.
 * No external AI API calls - uses only internal data analysis.
 */
class ContentSuggestionService
{
    /**
     * Get content suggestions based on popular content and trends
     */
    public function getContentSuggestions(int $limit = 5): array
    {
        // Cache for 30 minutes
        $minuteBlock = (int) (floor(now()->minute / 30) * 30);
        $cacheKey = 'content_suggestions_'.now()->format('Y-m-d-H').'-'.str_pad((string) $minuteBlock, 2, '0', STR_PAD_LEFT);

        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            // Get weekly popular content
            $popularPosts = Post::query()
                ->ofStatus('published')
                ->where('created_at', '>=', now()->subWeek())
                ->popular()
                ->limit(10)
                ->get(['title', 'summary', 'post_type', 'view_count']);

            $popularArticles = Article::where('status', 'published')
                ->where('created_at', '>=', now()->subWeek())
                ->orderBy('hit', 'desc')
                ->limit(10)
                ->get(['title', 'summary', 'hit']);

            // Get current trends
            $currentTrends = $this->getCurrentTrends();

            // Generate dynamic suggestions
            return $this->generateDynamicSuggestions($popularPosts, $popularArticles, $currentTrends, $limit);
        });
    }

    /**
     * Get current trends for financial/business content
     */
    private function getCurrentTrends(): array
    {
        return [
            'borsa' => ['BIST 100', 'hisse senetleri', 'endeksler', 'piyasa analizi', 'yatırım tavsiyeleri'],
            'kripto' => ['Bitcoin', 'Ethereum', 'kripto para', 'blockchain', 'DeFi', 'NFT'],
            'ekonomi' => ['enflasyon', 'faiz oranları', 'döviz kurları', 'merkez bankası', 'ekonomik büyüme'],
            'finans' => ['bankacılık', 'kredi', 'mortgage', 'sigorta', 'emeklilik', 'yatırım fonları'],
            'enerji' => ['petrol fiyatları', 'doğal gaz', 'elektrik', 'yenilenebilir enerji', 'enerji borsası'],
            'altın' => ['altın fiyatları', 'gümüş', 'değerli metaller', 'altın yatırımı', 'altın analizi'],
            'emlak' => ['konut fiyatları', 'kiralık piyasası', 'emlak yatırımı', 'şehir planlama'],
            'teknoloji' => ['fintech', 'yapay zeka', 'büyük veri', 'siber güvenlik', 'dijital dönüşüm'],
        ];
    }

    /**
     * Generate dynamic suggestions from popular content and trends
     */
    private function generateDynamicSuggestions($popularPosts, $popularArticles, array $currentTrends, int $limit): array
    {
        $suggestions = [];
        $usedTitles = [];

        // Generate suggestions from popular posts
        if ($popularPosts->count() > 0) {
            $postTitles = $popularPosts->pluck('title')->toArray();
            $suggestions = array_merge($suggestions, $this->createSuggestionsFromTitles($postTitles, 'news', $usedTitles));
        }

        // Generate suggestions from popular articles
        if ($popularArticles->count() > 0) {
            $articleTitles = $popularArticles->pluck('title')->toArray();
            $suggestions = array_merge($suggestions, $this->createSuggestionsFromTitles($articleTitles, 'news', $usedTitles));
        }

        // Generate suggestions from trends
        $trendSuggestions = $this->createSuggestionsFromTrends($currentTrends, $usedTitles);
        $suggestions = array_merge($suggestions, $trendSuggestions);

        // Add fallback suggestions if needed
        if (count($suggestions) < $limit) {
            $fallbackSuggestions = $this->getFallbackSuggestions();
            $needed = $limit - count($suggestions);
            $suggestions = array_merge($suggestions, array_slice($fallbackSuggestions, 0, $needed));
        }

        // Add current context
        $suggestions = $this->addCurrentContext($suggestions);

        // Limit and shuffle
        $suggestions = array_slice($suggestions, 0, $limit);
        shuffle($suggestions);

        return $suggestions;
    }

    /**
     * Create suggestions from titles
     */
    private function createSuggestionsFromTitles(array $titles, string $type, array &$usedTitles): array
    {
        $suggestions = [];
        $keywords = ['analiz', 'değerlendirme', 'güncel durum', 'trend analizi', 'piyasa görünümü'];

        foreach (array_slice($titles, 0, 3) as $title) {
            if (in_array($title, $usedTitles)) {
                continue;
            }

            $keyword = $keywords[array_rand($keywords)];
            $newTitle = $this->generateRelatedTitle($title, $keyword);

            $suggestions[] = [
                'title' => $newTitle,
                'type' => $type,
                'description' => "Popüler içeriklerinize dayalı güncel öneri - {$keyword}",
                'confidence' => rand(75, 95),
            ];

            $usedTitles[] = $title;
        }

        return $suggestions;
    }

    /**
     * Create suggestions from trends
     */
    private function createSuggestionsFromTrends(array $trends, array &$usedTitles): array
    {
        $suggestions = [];
        $trendCategories = array_keys($trends);

        foreach (array_slice($trendCategories, 0, 5) as $category) {
            $trendKeywords = $trends[$category];
            $randomKeyword = $trendKeywords[array_rand($trendKeywords)];

            $title = $this->generateTrendTitle($category, $randomKeyword);

            if (! in_array($title, $usedTitles)) {
                $suggestions[] = [
                    'title' => $title,
                    'type' => $this->getContentTypeForCategory($category),
                    'description' => "Güncel {$category} trendlerine dayalı öneri",
                    'confidence' => rand(80, 95),
                ];

                $usedTitles[] = $title;
            }
        }

        return $suggestions;
    }

    /**
     * Generate related title from original title
     */
    private function generateRelatedTitle(string $originalTitle, string $keyword): string
    {
        $transformations = [
            "{$originalTitle} - {$keyword}",
            "{$keyword}: {$originalTitle}",
            "Güncel {$keyword} - {$originalTitle}",
            "{$originalTitle} Piyasa Analizi",
        ];

        return $transformations[array_rand($transformations)];
    }

    /**
     * Generate trend-based title
     */
    private function generateTrendTitle(string $category, string $keyword): string
    {
        $templates = [
            'borsa' => [
                'BIST 100 Endeksi Haftalık Performans Analizi',
                "Borsa İstanbul'da En Çok Kazandıran Hisse Senetleri",
                "BIST 100'de Sektörel Dağılım ve Trend Analizi",
                "Borsa İstanbul'da Günlük İşlem Hacmi Değerlendirmesi",
                'BIST 100 Endeksinde Teknik Analiz ve Destek-Direnç Seviyeleri',
            ],
            'kripto' => [
                'Bitcoin Fiyat Analizi ve Yatırım Stratejisi',
                'Ethereum ve Altcoin Piyasalarında Güncel Durum',
                'Kripto Para Piyasalarında Volatilite Analizi',
                'DeFi Projeleri ve Yatırım Fırsatları',
                'Kripto Para Borsalarında İşlem Hacmi Değerlendirmesi',
            ],
            'ekonomi' => [
                'Merkez Bankası Faiz Kararı ve Piyasa Etkileri',
                'Enflasyon Verileri ve Ekonomik Görünüm',
                'Döviz Kurlarında Güncel Durum ve Beklentiler',
                'Ekonomik Büyüme Verileri ve Sektörel Analiz',
                'İşsizlik Oranları ve İstihdam Piyasası Değerlendirmesi',
            ],
            'finans' => [
                'Bankacılık Sektöründe Kredi Faiz Oranları',
                'Yatırım Fonları Performans Analizi ve Karşılaştırma',
                'Sigorta Sektöründe Yeni Ürünler ve Fiyatlandırma',
                'Finansal Teknolojiler (FinTech) ve Dijital Bankacılık',
                'Kredi Kartı ve Tüketici Kredilerinde Güncel Faiz Oranları',
            ],
            'enerji' => [
                'Petrol Fiyatları ve Enerji Sektörü Analizi',
                'Doğal Gaz Fiyatları ve Tüketim Trendleri',
                'Yenilenebilir Enerji Yatırımları ve Fırsatları',
                'Elektrik Fiyatları ve Enerji Piyasası Görünümü',
                'Enerji Borsası ve Emtia Fiyat Analizi',
            ],
            'altın' => [
                'Altın Fiyatları ve Değerli Metal Yatırım Stratejileri',
                'Altın-Gümüş Fiyat Oranı ve Teknik Analiz',
                'Değerli Metaller Piyasasında Güncel Durum',
                'Altın Yatırım Araçları ve Performans Karşılaştırması',
                'Altın Fiyatlarında Dolar Kuru Etkisi ve Analiz',
            ],
            'emlak' => [
                'Konut Fiyatları ve Emlak Piyasası Analizi',
                'Kiralık Konut Piyasasında Güncel Durum',
                'Emlak Yatırımı ve Getiri Oranları Değerlendirmesi',
                'Şehir Bazında Emlak Fiyat Trendleri',
                'Emlak Kredileri ve Mortgage Piyasası Görünümü',
            ],
            'teknoloji' => [
                'FinTech Sektöründe Yeni Gelişmeler ve Yatırım Fırsatları',
                'Yapay Zeka ve Büyük Veri Analizi Uygulamaları',
                'Siber Güvenlik ve Finansal Veri Koruma',
                'Dijital Dönüşüm ve Bankacılık Sektörü',
                'Blockchain Teknolojisi ve Finansal Uygulamalar',
            ],
        ];

        $categoryTemplates = $templates[$category] ?? $templates['ekonomi'];

        return $categoryTemplates[array_rand($categoryTemplates)];
    }

    /**
     * Get content type for category
     */
    private function getContentTypeForCategory(string $category): string
    {
        $typeMap = [
            'borsa' => 'news',
            'kripto' => 'news',
            'ekonomi' => 'news',
            'finans' => 'news',
            'enerji' => 'gallery',
            'altın' => 'video',
            'emlak' => 'gallery',
            'teknoloji' => 'video',
        ];

        return $typeMap[$category] ?? 'news';
    }

    /**
     * Add current context to suggestions
     */
    private function addCurrentContext(array $suggestions): array
    {
        $currentDate = now()->format('d.m.Y');
        $currentHour = now()->format('H:i');

        foreach ($suggestions as &$suggestion) {
            $suggestion['description'] .= " (Güncellenme: {$currentDate} {$currentHour})";
        }

        return $suggestions;
    }

    /**
     * Get fallback suggestions
     */
    private function getFallbackSuggestions(): array
    {
        return [
            [
                'title' => 'BIST 100 Endeksi Haftalık Performans Analizi',
                'type' => 'news',
                'description' => 'Borsa İstanbul\'da haftalık endeks hareketleri ve sektörel analiz',
                'confidence' => 90,
            ],
            [
                'title' => 'Bitcoin Fiyat Analizi ve Yatırım Stratejisi',
                'type' => 'news',
                'description' => 'Kripto para piyasalarında güncel fiyat hareketleri ve teknik analiz',
                'confidence' => 88,
            ],
            [
                'title' => 'Merkez Bankası Faiz Kararı ve Piyasa Etkileri',
                'type' => 'gallery',
                'description' => 'TCMB kararlarının borsa ve döviz piyasalarına etkisi',
                'confidence' => 92,
            ],
            [
                'title' => 'Altın Fiyatları ve Değerli Metal Yatırım Stratejileri',
                'type' => 'video',
                'description' => 'Altın ve değerli metallerde yatırım fırsatları ve analiz',
                'confidence' => 85,
            ],
            [
                'title' => 'Bankacılık Sektöründe Kredi Faiz Oranları',
                'type' => 'news',
                'description' => 'Ticari ve bireysel kredi faiz oranlarında güncel durum',
                'confidence' => 87,
            ],
            [
                'title' => 'Döviz Kurlarında Güncel Durum ve Beklentiler',
                'type' => 'news',
                'description' => 'USD/TRY, EUR/TRY kurlarında analiz ve yatırım önerileri',
                'confidence' => 89,
            ],
            [
                'title' => 'Yatırım Fonları Performans Analizi ve Karşılaştırma',
                'type' => 'gallery',
                'description' => 'Farklı yatırım fonlarının performans karşılaştırması',
                'confidence' => 86,
            ],
            [
                'title' => 'Petrol Fiyatları ve Enerji Sektörü Analizi',
                'type' => 'video',
                'description' => 'Enerji piyasalarında güncel durum ve yatırım fırsatları',
                'confidence' => 84,
            ],
        ];
    }
}
