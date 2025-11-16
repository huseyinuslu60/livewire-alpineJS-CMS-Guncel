<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Articles\Models\Article;
use Modules\Posts\Models\Post;

class AIContentSuggestionService
{
    public function getContentSuggestions($limit = 5)
    {
        // Cache süresi 30 dakika - 30 dakikalık bloklar halinde cache key oluştur
        // Fix: minuteBlock should be 0 or 30, not multiplied by 60
        $minuteBlock = (int) (floor(now()->minute / 30) * 30);
        $cacheKey = 'ai_content_suggestions_'.now()->format('Y-m-d-H').'-'.str_pad((string) $minuteBlock, 2, '0', STR_PAD_LEFT);

        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            // Haftalık en popüler içerikleri al
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

            // Güncel trendleri al
            $currentTrends = $this->getCurrentTrends();

            // Önce AI servisini dene
            $contentData = [
                'popular_posts' => $popularPosts->toArray(),
                'popular_articles' => $popularArticles->toArray(),
                'trends' => $currentTrends,
            ];

            $aiSuggestions = $this->callAIService($contentData);

            // Eğer AI'dan yeterli öneri gelirse onları kullan
            if (! empty($aiSuggestions) && count($aiSuggestions) >= 3) {
                return array_slice($aiSuggestions, 0, $limit);
            }

            // AI başarısız olursa dinamik öneriler oluştur
            $suggestions = $this->generateDynamicSuggestions($popularPosts, $popularArticles, $currentTrends, $limit);

            return $suggestions;
        });
    }

    private function getCurrentTrends()
    {
        // Borsa ve finans sitesi için özel trendler
        $trends = [
            'borsa' => ['BIST 100', 'hisse senetleri', 'endeksler', 'piyasa analizi', 'yatırım tavsiyeleri'],
            'kripto' => ['Bitcoin', 'Ethereum', 'kripto para', 'blockchain', 'DeFi', 'NFT'],
            'ekonomi' => ['enflasyon', 'faiz oranları', 'döviz kurları', 'merkez bankası', 'ekonomik büyüme'],
            'finans' => ['bankacılık', 'kredi', 'mortgage', 'sigorta', 'emeklilik', 'yatırım fonları'],
            'enerji' => ['petrol fiyatları', 'doğal gaz', 'elektrik', 'yenilenebilir enerji', 'enerji borsası'],
            'altın' => ['altın fiyatları', 'gümüş', 'değerli metaller', 'altın yatırımı', 'altın analizi'],
            'emlak' => ['konut fiyatları', 'kiralık piyasası', 'emlak yatırımı', 'şehir planlama'],
            'teknoloji' => ['fintech', 'yapay zeka', 'büyük veri', 'siber güvenlik', 'dijital dönüşüm'],
        ];

        return $trends;
    }

    private function generateDynamicSuggestions($popularPosts, $popularArticles, $currentTrends, $limit)
    {
        $suggestions = [];
        $usedTitles = [];

        // Popüler içeriklerden dinamik öneriler oluştur
        if ($popularPosts->count() > 0) {
            $postTitles = $popularPosts->pluck('title')->toArray();
            $suggestions = array_merge($suggestions, $this->createSuggestionsFromTitles($postTitles, 'news', $usedTitles));
        }

        if ($popularArticles->count() > 0) {
            $articleTitles = $popularArticles->pluck('title')->toArray();
            $suggestions = array_merge($suggestions, $this->createSuggestionsFromTitles($articleTitles, 'news', $usedTitles));
        }

        // Güncel trendlerden öneriler oluştur (daha fazla)
        $trendSuggestions = $this->createSuggestionsFromTrends($currentTrends, $usedTitles);
        $suggestions = array_merge($suggestions, $trendSuggestions);

        // Eğer yeterli öneri yoksa, fallback önerilerini ekle
        if (count($suggestions) < $limit) {
            $fallbackSuggestions = $this->getFallbackSuggestions();
            $needed = $limit - count($suggestions);
            $suggestions = array_merge($suggestions, array_slice($fallbackSuggestions, 0, $needed));
        }

        // Güncel tarih ve saat bilgisi ekle
        $suggestions = $this->addCurrentContext($suggestions);

        // Limit'e göre kes ve karıştır
        $suggestions = array_slice($suggestions, 0, $limit);
        shuffle($suggestions);

        return $suggestions;
    }

    private function createSuggestionsFromTitles($titles, $type, &$usedTitles)
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

    private function createSuggestionsFromTrends($trends, &$usedTitles)
    {
        $suggestions = [];
        $trendCategories = array_keys($trends);

        // Daha fazla kategori kullan (5 tane)
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

    private function generateRelatedTitle($originalTitle, $keyword)
    {
        // Basit title transformation
        $transformations = [
            "{$originalTitle} - {$keyword}",
            "{$keyword}: {$originalTitle}",
            "Güncel {$keyword} - {$originalTitle}",
            "{$originalTitle} Piyasa Analizi",
        ];

        return $transformations[array_rand($transformations)];
    }

    private function generateTrendTitle($category, $keyword)
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

    private function getContentTypeForCategory($category)
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

    private function addCurrentContext($suggestions)
    {
        $currentDate = now()->format('d.m.Y');
        $currentHour = now()->format('H:i');

        foreach ($suggestions as &$suggestion) {
            $suggestion['description'] .= " (Güncellenme: {$currentDate} {$currentHour})";
        }

        return $suggestions;
    }

    private function callAIService($contentData)
    {
        // Önce OpenAI'yi dene, sonra alternatif modeller
        $aiResponse = $this->tryOpenAI($contentData);

        if ($aiResponse) {
            $parsedResponse = $this->parseAIResponse($aiResponse);
            if (! empty($parsedResponse) && count($parsedResponse) >= 3) {
                return $parsedResponse;
            }
        }

        // OpenAI başarısız olursa alternatif modelleri dene
        $aiResponse = $this->tryClaude($contentData);
        if ($aiResponse) {
            $parsedResponse = $this->parseAIResponse($aiResponse);
            if (! empty($parsedResponse) && count($parsedResponse) >= 3) {
                return $parsedResponse;
            }
        }

        // Tüm AI servisleri başarısız olursa boş dizi döndür (fallback'e geçilecek)
        \Log::warning('AI servisleri başarısız oldu, fallback önerileri kullanılacak');

        return [];
    }

    private function tryOpenAI($contentData, $retries = 2)
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            \Log::warning('OpenAI API key bulunamadı');

            return null;
        }

        $lastException = null;

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                if ($attempt > 0) {
                    // Exponential backoff: 1s, 2s, 4s
                    sleep(min(pow(2, $attempt - 1), 4));
                    \Log::info("OpenAI retry attempt {$attempt}");
                }

                $response = Http::timeout(30)
                    ->retry(1, 100) // 1 retry with 100ms delay for network issues
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Sen Türkiye\'nin önde gelen finans ve borsa haber sitesinin deneyimli editörüsün. Popüler haber ve makalelere bakarak, borsa, finans, ekonomi, kripto para, yatırım konularında yeni içerik önerileri sunuyorsun. Önerilerin gerçekçi, profesyonel, güncel ve seo uyumlu olmalı. Sadece Türkçe yanıt ver. Her öneri için numara ile başla (1., 2., 3. gibi). ve tarih'.date('d.m.Y').' önerileri bu tarihe göre ver',
                            ],
                            [
                                'role' => 'user',
                                'content' => $this->buildPrompt($contentData),
                            ],
                        ],
                        'max_tokens' => 1500,
                        'temperature' => 0.8,
                        'top_p' => 0.9,
                        'frequency_penalty' => 0.1,
                        'presence_penalty' => 0.1,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    $errorBody = $response->body();
                    $statusCode = $response->status();
                    \Log::error("OpenAI API Error [{$statusCode}]: {$errorBody}");

                    // Don't retry on 4xx errors (client errors)
                    if ($statusCode >= 400 && $statusCode < 500) {
                        break;
                    }
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;
                \Log::warning("OpenAI connection error (attempt {$attempt}): ".$e->getMessage());
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $lastException = $e;
                \Log::warning("OpenAI request error (attempt {$attempt}): ".$e->getMessage());
            } catch (\Exception $e) {
                $lastException = $e;
                \Log::error("OpenAI API Error (attempt {$attempt}): ".$e->getMessage());
                // Don't retry on unexpected errors
                break;
            }
        }

        if ($lastException) {
            \Log::error('OpenAI API failed after all retries: '.$lastException->getMessage());
        }

        return null;
    }

    private function tryClaude($contentData)
    {
        try {
            $apiKey = config('services.anthropic.api_key');

            if (empty($apiKey)) {
                \Log::warning('Anthropic API key bulunamadı');

                return null;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-5-sonnet-20241022', // En güncel Claude modeli
                    'max_tokens' => 1500,
                    'system' => 'Sen Türkiye\'nin önde gelen finans ve borsa haber sitesinin deneyimli editörüsün. Popüler haber ve makalelere bakarak, borsa, finans, ekonomi, kripto para, yatırım konularında yeni içerik önerileri sunuyorsun. Önerilerin gerçekçi, profesyonel, güncel ve seo uyumlu olmalı. Sadece Türkçe yanıt ver. Her öneri için numara ile başla (1., 2., 3. gibi).',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($contentData),
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                \Log::error('Claude API Error: '.$response->body());
            }

        } catch (\Exception $e) {
            \Log::error('Claude API Error: '.$e->getMessage());
        }

        return null;
    }

    private function buildPrompt($contentData)
    {
        $popularTitles = collect($contentData['popular_posts'])
            ->pluck('title')
            ->take(5)
            ->implode("\n- ");

        $popularArticles = collect($contentData['popular_articles'])
            ->pluck('title')
            ->take(5)
            ->implode("\n- ");

        $trends = collect($contentData['trends'] ?? [])
            ->flatten()
            ->take(10)
            ->implode(', ');

        return "Sen bir borsa ve finans haber sitesi editörüsün.

Haftalık en çok okunan haberler:
- {$popularTitles}

Haftalık en çok okunan makaleler:
- {$popularArticles}

Güncel trendler: {$trends}

Bu verilere dayanarak, borsa, finans, ekonomi, kripto para, yatırım konularında yazılabilecek 5 yeni haber başlığı öner.

Her başlık için:
1. Başlık numara ile başlasın (1., 2., 3. gibi)
2. Başlıklar Türkçe olsun ve finansal terimler içersin
3. Başlıklar gerçekçi, güncel ve SEO uyumlu olsun
4. Her başlık tek satırda olsun

Sadece başlıkları listele, açıklama ekleme. Örnek format:
1. BIST 100 Endeksi Haftalık Performans Analizi
2. Bitcoin Fiyat Analizi ve Yatırım Stratejisi
3. Merkez Bankası Faiz Kararı ve Piyasa Etkileri";
    }

    private function parseAIResponse($response)
    {
        // OpenAI ve Claude için farklı response formatları
        $content = '';

        if (isset($response['choices'][0]['message']['content'])) {
            // OpenAI format
            $content = $response['choices'][0]['message']['content'];
        } elseif (isset($response['content'][0]['text'])) {
            // Claude format
            $content = $response['content'][0]['text'];
        }

        if (empty($content)) {
            \Log::warning('AI yanıtı boş');

            return [];
        }

        // AI yanıtını parse et ve yapılandır
        $suggestions = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            // Numara ile başlayan satırları bul (1., 2., 3. gibi)
            if (preg_match('/^\d+[\.\)]\s*(.+?)(?:\s*[-–—]\s*.+)?$/', $line, $matches)) {
                $title = trim($matches[1]);

                // Başlık çok kısa veya boşsa atla
                if (strlen($title) < 10) {
                    continue;
                }

                // İçerik türünü belirle
                $type = 'news';
                $titleLower = mb_strtolower($title);
                if (stripos($titleLower, 'galeri') !== false || stripos($titleLower, 'görsel') !== false || stripos($titleLower, 'fotoğraf') !== false) {
                    $type = 'gallery';
                } elseif (stripos($titleLower, 'video') !== false || stripos($titleLower, 'canlı') !== false || stripos($titleLower, 'yayın') !== false) {
                    $type = 'video';
                }

                $suggestions[] = [
                    'title' => $title,
                    'type' => $type,
                    'description' => 'AI tarafından oluşturulan güncel öneri',
                    'confidence' => rand(85, 98), // AI önerileri daha yüksek güven
                ];
            }
        }

        // Eğer yeterli öneri bulunduysa döndür
        if (count($suggestions) >= 3) {
            return array_slice($suggestions, 0, 5);
        }

        // Yeterli öneri yoksa boş döndür (fallback'e geçilecek)
        \Log::warning('AI yeterli öneri üretemedi: '.count($suggestions).' öneri bulundu');

        return $suggestions;
    }

    private function getFallbackSuggestions()
    {
        // AI servisi çalışmazsa borsa ve finans odaklı manuel öneriler
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
