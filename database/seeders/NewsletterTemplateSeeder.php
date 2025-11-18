<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateSeeder extends Seeder
{
    public function run()
    {
        // Mevcut template'leri temizle
        NewsletterTemplate::truncate();

        $templates = [
            [
                'name' => 'Modern Finans',
                'slug' => 'modern-finans',
                'description' => 'Modern ve profesyonel finans bülteni tasarımı',
                'header_html' => $this->getModernFinansHeader(),
                'content_html' => $this->getModernFinansContent(),
                'footer_html' => $this->getModernFinansFooter(),
                'styles' => [
                    'primary_color' => '#2563eb',
                    'secondary_color' => '#1e40af',
                    'text_color' => '#ffffff',
                    'background_color' => '#f8fafc',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Klasik Borsa',
                'slug' => 'klasik-borsa',
                'description' => 'Geleneksel borsa odası tarzı profesyonel bülten',
                'header_html' => $this->getKlasikBorsaHeader(),
                'content_html' => $this->getKlasikBorsaContent(),
                'footer_html' => $this->getKlasikBorsaFooter(),
                'styles' => [
                    'primary_color' => '#dc2626',
                    'secondary_color' => '#991b1b',
                    'text_color' => '#ffffff',
                    'background_color' => '#ffffff',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Minimalist Profesyonel',
                'slug' => 'minimalist-profesyonel',
                'description' => 'Sade ve şık minimalist finans bülteni',
                'header_html' => $this->getMinimalistProfesyonelHeader(),
                'content_html' => $this->getMinimalistProfesyonelContent(),
                'footer_html' => $this->getMinimalistProfesyonelFooter(),
                'styles' => [
                    'primary_color' => '#111827',
                    'secondary_color' => '#6b7280',
                    'text_color' => '#111827',
                    'background_color' => '#ffffff',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Yeşil Piyasa',
                'slug' => 'yesil-piyasa',
                'description' => 'Yeşil tonlarda modern finans bülteni',
                'header_html' => $this->getYesilPiyasaHeader(),
                'content_html' => $this->getYesilPiyasaContent(),
                'footer_html' => $this->getYesilPiyasaFooter(),
                'styles' => [
                    'primary_color' => '#059669',
                    'secondary_color' => '#047857',
                    'text_color' => '#ffffff',
                    'background_color' => '#f0fdf4',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Yeşil Yatırım',
                'slug' => 'yesil-yatirim',
                'description' => 'Yeşil tonlarda profesyonel yatırım bülteni',
                'header_html' => $this->getYesilYatirimHeader(),
                'content_html' => $this->getYesilYatirimContent(),
                'footer_html' => $this->getYesilYatirimFooter(),
                'styles' => [
                    'primary_color' => '#10b981',
                    'secondary_color' => '#059669',
                    'text_color' => '#ffffff',
                    'background_color' => '#ecfdf5',
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Premium Finans',
                'slug' => 'premium-finans',
                'description' => 'Premium kurumsal finans bülteni - Altın ve koyu tonlar',
                'header_html' => $this->getPremiumFinansHeader(),
                'content_html' => $this->getPremiumFinansContent(),
                'footer_html' => $this->getPremiumFinansFooter(),
                'styles' => [
                    'primary_color' => '#1f2937',
                    'secondary_color' => '#d97706',
                    'text_color' => '#ffffff',
                    'background_color' => '#fefce8',
                ],
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($templates as $template) {
            NewsletterTemplate::create($template);
        }
    }

    // Modern Finans Template
    private function getModernFinansHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 50px 30px; text-align: center; color: {{ text_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; backdrop-filter: blur(20px);"></div>
            <div style="position: absolute; bottom: -40px; left: -40px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%; backdrop-filter: blur(15px);"></div>
            
            <div style="position: relative; z-index: 2; max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; flex-wrap: wrap;">
                    <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-bottom: 10px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 6px 24px rgba(0,0,0,0.15);">
                        <span style="font-size: 28px;">📊</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 38px; font-weight: 800; letter-spacing: -0.8px; line-height: 1.2; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">Borsanın Gündemi</h1>
                        <p style="margin: 6px 0 0 0; font-size: 16px; opacity: 0.95; font-weight: 400;">Modern Finans Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <p style="margin: 0; font-size: 20px; opacity: 0.98; font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 8px 0 0 0; font-size: 15px; opacity: 0.9;">Finans dünyasından en güncel haberler ve profesyonel analizler</p>
                </div>

                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.25); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">📈 Piyasa Analizi</span>
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">💼 Yatırım Önerileri</span>
                </div>
            </div>
        </div>';
    }

    private function getModernFinansContent()
    {
        return '
        <div style="padding: 45px 30px; background: {{ background_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 35px;">
                    <h2 style="color: {{ primary_color }}; margin-bottom: 12px; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">📈 Piyasa Güncellemeleri</h2>
                    <p style="color: #475569; font-size: 16px; margin: 0; font-weight: 400;">Finans dünyasından en güncel haberler ve profesyonel analizler</p>
                </div>

                <div style="background: white; border-radius: 14px; padding: 35px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid rgba(0, 0, 0, 0.05);">
                    {{ $newsletterContent }}
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 25px;">
                    <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 14px; text-align: center; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);">
                        <div style="font-size: 28px; margin-bottom: 10px;">📈</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Piyasa Performansı</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95;">Günlük piyasa analizleri ve trendler</p>
                    </div>
                    <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 14px; text-align: center; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);">
                        <div style="font-size: 28px; margin-bottom: 10px;">💼</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Yatırım Stratejileri</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95;">Uzman yatırım önerileri</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getModernFinansFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 45px 30px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="margin-bottom: 35px;">
                    <h3 style="color: {{ text_color }}; margin: 0 0 18px 0; font-size: 24px; font-weight: 800;">📊 Borsanın Gündemi</h3>
                    <p style="margin: 0 0 12px 0; opacity: 0.98; font-size: 15px; line-height: 1.6;">Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">tıklayınız</a></p>
                    <p style="margin: 0 0 18px 0; opacity: 0.9; font-size: 13px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
                </div>

                <div style="margin: 35px 0; padding: 30px; background: rgba(255,255,255,0.12); border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }};">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                    <div style="opacity: 0.95; font-size: 14px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
                </div>

                <div style="margin: 35px 0;">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }};">Bizi Takip Edin</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 54px; height: 54px; background: #1877f2; border-radius: 14px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(24, 119, 242, 0.3); transition: transform 0.2s;">
                            <span style="color: white; font-weight: bold; font-size: 22px;">f</span>
                        </a>
                        <a href="#" style="width: 54px; height: 54px; background: #000000; border-radius: 14px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3); transition: transform 0.2s;">
                            <span style="color: white; font-weight: bold; font-size: 22px;">𝕏</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.2);">
                    <p style="margin: 0 0 12px 0; font-size: 12px; opacity: 0.85;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                    <p style="margin: 0; font-size: 12px; opacity: 0.85;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">tıklayınız</a></p>
                </div>
            </div>
        </div>';
    }

    // Klasik Borsa Template
    private function getKlasikBorsaHeader()
    {
        return '
        <div style="background: {{ primary_color }}; padding: 0; color: {{ text_color }}; font-family: \'Georgia\', \'Times New Roman\', serif;">
            <div style="background: {{ secondary_color }}; padding: 8px 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 2px solid {{ primary_color }};">
                BORSANIN GÜNDEMİ | FINANCIAL BRIEFING
            </div>

            <div style="padding: 35px 20px; border-bottom: 4px solid {{ secondary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap;">
                    <div>
                        <h1 style="margin: 0; font-size: 32px; font-weight: 900; color: {{ text_color }}; letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: {{ text_color }}; font-weight: 400; opacity: 0.85;">Profesyonel Finans Bülteni</p>
                    </div>
                    <div style="text-align: right; color: {{ text_color }}; font-size: 11px; opacity: 0.9; margin-top: 12px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>

                <div style="background: {{ secondary_color }}; padding: 10px 16px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; font-weight: bold; flex-wrap: wrap; gap: 8px;">
                    <span>📈 BIST 100: 8,245.67 (+1.2%)</span>
                    <span>💱 USD/TRY: 32.45 (+0.8%)</span>
                    <span>⏰ Son Güncelleme: 15:30</span>
                </div>
            </div>
        </div>';
    }

    private function getKlasikBorsaContent()
    {
        return '
        <div style="background: {{ background_color }}; padding: 0; font-family: \'Georgia\', \'Times New Roman\', serif;">
            <div style="background: white; margin: 18px; border: 2px solid {{ primary_color }}; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
                <div style="background: {{ primary_color }}; color: {{ text_color }}; padding: 12px 20px; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    📊 PİYASA HABERLERİ
                </div>

                <div style="padding: 22px; color: #1f2937; line-height: 1.7;">
                    {{ $newsletterContent }}
                </div>
            </div>
        </div>';
    }

    private function getKlasikBorsaFooter()
    {
        return '
        <div style="background: {{ primary_color }}; color: {{ text_color }}; font-family: \'Georgia\', \'Times New Roman\', serif; font-size: 12px;">
            <div style="background: {{ secondary_color }}; padding: 16px 20px; border-bottom: 2px solid {{ primary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: {{ text_color }};">BORSANIN GÜNDEMİ</h3>
                        <p style="margin: 5px 0 0 0; font-size: 11px; color: {{ text_color }}; opacity: 0.85;">Profesyonel Finans Bülteni</p>
                    </div>
                    <div style="text-align: right; font-size: 11px; color: {{ text_color }}; opacity: 0.9; margin-top: 8px;">
                        <div style="font-weight: bold; margin-bottom: 4px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>
            </div>

            <div style="padding: 22px; background: {{ primary_color }};">
                <p style="margin: 0 0 16px 0; color: {{ text_color }}; line-height: 1.6; font-size: 12px; opacity: 0.95;">
                    Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik.
                    Daha fazla analiz ve güncel veriler için <a href="#" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">web sitemizi ziyaret edin</a>.
                </p>
                <p style="margin: 0 0 20px 0; color: {{ text_color }}; font-size: 11px; opacity: 0.85;">
                    Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.
                </p>
            </div>

            <div style="background: {{ secondary_color }}; padding: 22px; border-top: 2px solid {{ primary_color }};">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 22px;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK A.Ş.</h4>
                        <div style="font-size: 11px; color: {{ text_color }}; line-height: 1.6; opacity: 0.9;">
                            <div>📍 Ergenekon Mah. Cumhuriyet Cad.</div>
                            <div>📍 Efser Han No: 181 Kat: 8</div>
                            <div>📍 Harbiye - Şişli - İstanbul</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">İLETİŞİM</h4>
                        <div style="font-size: 11px; color: {{ text_color }}; line-height: 1.6; opacity: 0.9;">
                            <div>📞 Tel: 0 212 294 11 69</div>
                            <div>📞 Mobil: 0 530 849 88 48</div>
                            <div>📠 Faks: 0 212 238 72 07</div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin: 22px 0;">
                    <div style="font-size: 12px; font-weight: bold; color: {{ text_color }}; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">BİZİ TAKİP EDİN</div>
                    <div style="display: flex; justify-content: center; gap: 16px;">
                        <a href="#" style="width: 38px; height: 38px; background: #1877f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(24, 119, 242, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 14px;">f</span>
                        </a>
                        <a href="#" style="width: 38px; height: 38px; background: #000000; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 14px;">𝕏</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: {{ primary_color }}; padding: 16px 20px; border-top: 2px solid {{ secondary_color }}; text-align: center;">
                <p style="margin: 0 0 10px 0; font-size: 10px; color: {{ text_color }}; opacity: 0.85;">
                    Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.
                </p>
                <p style="margin: 0; font-size: 10px; color: {{ text_color }}; opacity: 0.85;">
                    Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">tıklayınız</a>
                </p>
            </div>
        </div>';
    }

    // Minimalist Profesyonel Template
    private function getMinimalistProfesyonelHeader()
    {
        return '
        <div style="background: {{ background_color }}; padding: 50px 30px; text-align: center; color: {{ text_color }}; border-bottom: 2px solid {{ primary_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; flex-wrap: wrap;">
                    <div style="width: 56px; height: 56px; background: {{ primary_color }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                        <span style="font-size: 26px; color: {{ background_color }};">📊</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 36px; font-weight: 300; color: {{ primary_color }}; letter-spacing: -1px; line-height: 1.2;">Borsanın Gündemi</h1>
                        <p style="margin: 6px 0 0 0; font-size: 14px; color: {{ secondary_color }}; font-weight: 400;">Minimalist Finans Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <p style="margin: 0; font-size: 18px; color: {{ primary_color }}; font-weight: 400;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 8px 0 0 0; font-size: 14px; color: {{ secondary_color }}; opacity: 0.75;">Sade ve etkili finansal analizler</p>
                </div>

                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <span style="background: {{ primary_color }}; color: {{ background_color }}; padding: 7px 16px; border-radius: 18px; font-size: 13px; font-weight: 500; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);">#tarih#</span>
                    <span style="background: {{ secondary_color }}; color: {{ background_color }}; padding: 7px 16px; border-radius: 18px; font-size: 13px; font-weight: 500; opacity: 0.2; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);">📈 Piyasa</span>
                    <span style="background: {{ secondary_color }}; color: {{ background_color }}; padding: 7px 16px; border-radius: 18px; font-size: 13px; font-weight: 500; opacity: 0.2; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);">💼 Yatırım</span>
                </div>
            </div>
        </div>';
    }

    private function getMinimalistProfesyonelContent()
    {
        return '
        <div style="padding: 45px 30px; background: {{ background_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 35px;">
                    <h2 style="color: {{ primary_color }}; margin-bottom: 12px; font-size: 26px; font-weight: 300; letter-spacing: -0.5px;">📊 Piyasa Analizi</h2>
                    <p style="color: {{ secondary_color }}; font-size: 14px; margin: 0; font-weight: 400; opacity: 0.75;">Sade ve etkili finansal analizler</p>
                </div>

                <div style="background: white; border-radius: 12px; padding: 35px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06); border: 1px solid rgba(0, 0, 0, 0.08);">
                    {{ $newsletterContent }}
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 25px;">
                    <div style="background: {{ primary_color }}; color: {{ background_color }}; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                        <div style="font-size: 28px; margin-bottom: 10px;">📈</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: {{ background_color }};">Piyasa Analizi</h3>
                        <p style="margin: 0; font-size: 13px; color: {{ background_color }}; opacity: 0.95; line-height: 1.5;">Sade piyasa analizleri ve trend raporları</p>
                    </div>
                    <div style="background: {{ secondary_color }}; color: {{ background_color }}; padding: 25px; border-radius: 12px; text-align: center; border: 2px solid {{ primary_color }}; opacity: 0.15; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
                        <div style="font-size: 28px; margin-bottom: 10px;">💼</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">Yatırım</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.8; line-height: 1.5;">Minimal yatırım stratejileri</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getMinimalistProfesyonelFooter()
    {
        return '
        <div style="background: {{ background_color }}; padding: 45px 30px; text-align: center; color: {{ text_color }}; font-size: 14px; border-top: 2px solid {{ primary_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="margin-bottom: 35px;">
                    <h3 style="color: {{ primary_color }}; margin: 0 0 18px 0; font-size: 22px; font-weight: 300; letter-spacing: -0.5px;">📊 Borsanın Gündemi</h3>
                    <p style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.6; color: {{ text_color }}; opacity: 0.85;">Sayın <strong>#isim#</strong>, sade ve etkili finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
                    <p style="margin: 0 0 18px 0; font-size: 12px; color: {{ secondary_color }}; opacity: 0.75;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
                </div>

                <div style="margin: 35px 0; padding: 30px; background: white; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);">
                    <div style="font-weight: 600; margin-bottom: 18px; font-size: 14px; color: {{ primary_color }}; letter-spacing: 0.3px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                    <div style="margin-bottom: 8px; font-size: 13px; color: {{ secondary_color }}; opacity: 0.8; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                    <div style="margin-bottom: 8px; font-size: 13px; color: {{ secondary_color }}; opacity: 0.8; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                    <div style="margin-bottom: 8px; font-size: 13px; color: {{ secondary_color }}; opacity: 0.8; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                    <div style="font-size: 13px; color: {{ secondary_color }}; opacity: 0.8; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
                </div>

                <div style="margin: 35px 0;">
                    <div style="font-weight: 600; margin-bottom: 18px; font-size: 14px; color: {{ primary_color }}; letter-spacing: 0.3px;">Bizi Takip Edin</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 46px; height: 46px; background: #1877f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 10px rgba(24, 119, 242, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 18px;">f</span>
                        </a>
                        <a href="#" style="width: 46px; height: 46px; background: #000000; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 18px;">𝕏</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid rgba(0, 0, 0, 0.08);">
                    <p style="margin: 0 0 12px 0; font-size: 11px; color: {{ secondary_color }}; opacity: 0.7;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                    <p style="margin: 0; font-size: 11px; color: {{ secondary_color }}; opacity: 0.7;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
                </div>
            </div>
        </div>';
    }

    // Yeşil Piyasa Template
    private function getYesilPiyasaHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 50px 30px; text-align: center; color: {{ text_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; backdrop-filter: blur(20px);"></div>
            <div style="position: absolute; bottom: -40px; left: -40px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%; backdrop-filter: blur(15px);"></div>
            
            <div style="position: relative; z-index: 2; max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; flex-wrap: wrap;">
                    <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.25); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-bottom: 10px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 6px 24px rgba(0,0,0,0.15);">
                        <span style="font-size: 28px;">📈</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 38px; font-weight: 800; letter-spacing: -0.8px; line-height: 1.2; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">Borsanın Gündemi</h1>
                        <p style="margin: 6px 0 0 0; font-size: 16px; opacity: 0.95; font-weight: 400;">Yeşil Piyasa Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <p style="margin: 0; font-size: 20px; opacity: 0.98; font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 8px 0 0 0; font-size: 15px; opacity: 0.9;">Piyasa analizleri ve finansal güncellemeler</p>
                </div>

                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.25); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">📊 Piyasa Raporu</span>
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">💰 Finansal Analiz</span>
                </div>
            </div>
        </div>';
    }

    private function getYesilPiyasaContent()
    {
        return '
        <div style="padding: 45px 30px; background: {{ background_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 35px;">
                    <h2 style="color: {{ primary_color }}; margin-bottom: 12px; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">📊 Piyasa Güncellemeleri</h2>
                    <p style="color: #065f46; font-size: 16px; margin: 0; font-weight: 400;">Güncel piyasa analizleri ve finansal trendler</p>
                </div>

                <div style="background: white; border-radius: 14px; padding: 35px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid rgba(5, 150, 105, 0.1);">
                    {{ $newsletterContent }}
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 25px;">
                    <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 14px; text-align: center; box-shadow: 0 6px 20px rgba(5, 150, 105, 0.15);">
                        <div style="font-size: 28px; margin-bottom: 10px;">📊</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Piyasa Analizi</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95;">Günlük piyasa raporları ve trendler</p>
                    </div>
                    <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 14px; text-align: center; box-shadow: 0 6px 20px rgba(5, 150, 105, 0.15);">
                        <div style="font-size: 28px; margin-bottom: 10px;">💰</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Finansal Analiz</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95;">Detaylı finansal analizler</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getYesilPiyasaFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 45px 30px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="margin-bottom: 35px;">
                    <h3 style="color: {{ text_color }}; margin: 0 0 18px 0; font-size: 24px; font-weight: 800;">📊 Borsanın Gündemi</h3>
                    <p style="margin: 0 0 12px 0; opacity: 0.98; font-size: 15px; line-height: 1.6;">Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">tıklayınız</a></p>
                    <p style="margin: 0 0 18px 0; opacity: 0.9; font-size: 13px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
                </div>

                <div style="margin: 35px 0; padding: 30px; background: rgba(255,255,255,0.12); border-radius: 14px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }};">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                    <div style="opacity: 0.95; font-size: 14px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
                </div>

                <div style="margin: 35px 0;">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }};">Bizi Takip Edin</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 54px; height: 54px; background: #1877f2; border-radius: 14px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(24, 119, 242, 0.3); transition: transform 0.2s;">
                            <span style="color: white; font-weight: bold; font-size: 22px;">f</span>
                        </a>
                        <a href="#" style="width: 54px; height: 54px; background: #000000; border-radius: 14px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3); transition: transform 0.2s;">
                            <span style="color: white; font-weight: bold; font-size: 22px;">𝕏</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.2);">
                    <p style="margin: 0 0 12px 0; font-size: 12px; opacity: 0.85;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                    <p style="margin: 0; font-size: 12px; opacity: 0.85;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">tıklayınız</a></p>
                </div>
            </div>
        </div>';
    }

    // Yeşil Yatırım Template
    private function getYesilYatirimHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 50px 30px; text-align: center; color: {{ text_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -60px; right: -60px; width: 160px; height: 160px; background: radial-gradient(circle, rgba(255,255,255,0.1), transparent); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -50px; left: -50px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.08), transparent); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2; max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; flex-wrap: wrap;">
                    <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-bottom: 10px; box-shadow: 0 0 25px rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.25);">
                        <span style="font-size: 28px;">💼</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 38px; font-weight: 900; text-shadow: 0 0 20px rgba(255,255,255,0.3); letter-spacing: -1px; line-height: 1.2;">Borsanın Gündemi</h1>
                        <p style="margin: 8px 0 0 0; font-size: 16px; color: rgba(255,255,255,0.95); font-weight: 400; letter-spacing: 0.5px;">Yatırım Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <p style="margin: 0; font-size: 20px; color: rgba(255,255,255,0.98); font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 8px 0 0 0; font-size: 14px; color: rgba(255,255,255,0.9); opacity: 0.95;">Yatırım stratejileri ve finansal rehberlik</p>
                </div>

                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.15); padding: 8px 18px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(255,255,255,0.25); backdrop-filter: blur(10px);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.12); padding: 8px 18px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">💎 Yatırım Stratejileri</span>
                    <span style="background: rgba(255,255,255,0.12); padding: 8px 18px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">📊 Portföy Analizi</span>
                </div>
            </div>
        </div>';
    }

    private function getYesilYatirimContent()
    {
        return '
        <div style="padding: 45px 30px; background: {{ background_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 35px;">
                    <h2 style="color: {{ primary_color }}; margin-bottom: 12px; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">💼 Yatırım Stratejileri</h2>
                    <p style="color: #047857; font-size: 16px; margin: 0; font-weight: 400;">Profesyonel yatırım önerileri ve portföy analizleri</p>
                </div>

                <div style="background: white; border-radius: 18px; padding: 35px; border: 2px solid rgba(16, 185, 129, 0.1); box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);">
                    {{ $newsletterContent }}
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 25px;">
                    <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.2); border: 2px solid rgba(255,255,255,0.1);">
                        <div style="font-size: 28px; margin-bottom: 10px;">💎</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Yatırım Stratejileri</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.5;">Uzman yatırım önerileri</p>
                    </div>
                    <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.2); border: 2px solid rgba(255,255,255,0.1);">
                        <div style="font-size: 28px; margin-bottom: 10px;">📊</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Portföy Analizi</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.5;">Detaylı portföy değerlendirmeleri</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getYesilYatirimFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 45px 30px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="margin-top: 10px; margin-bottom: 35px;">
                    <h3 style="color: {{ text_color }}; margin: 0 0 18px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; text-shadow: 0 0 15px rgba(255,255,255,0.2);">💼 Borsanın Gündemi</h3>
                    <p style="margin: 0 0 12px 0; opacity: 0.98; font-size: 15px; line-height: 1.7;">Sayın <strong>#isim#</strong>, yatırım stratejileri ve finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">tıklayınız</a></p>
                    <p style="margin: 0 0 18px 0; opacity: 0.9; font-size: 13px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
                </div>

                <div style="margin: 35px 0; padding: 30px; background: rgba(255,255,255,0.1); border-radius: 18px; border: 2px solid rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                    <div style="opacity: 0.95; font-size: 14px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
                </div>

                <div style="margin: 35px 0;">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ text_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 54px; height: 54px; background: #1877f2; border-radius: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 0 20px rgba(24, 119, 242, 0.4); border: 2px solid rgba(255,255,255,0.1);">
                            <span style="color: white; font-weight: bold; font-size: 22px;">f</span>
                        </a>
                        <a href="#" style="width: 54px; height: 54px; background: #000000; border-radius: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 0 20px rgba(0, 0, 0, 0.4); border: 2px solid rgba(255,255,255,0.1);">
                            <span style="color: white; font-weight: bold; font-size: 22px;">𝕏</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid rgba(255,255,255,0.15);">
                    <p style="margin: 0 0 12px 0; font-size: 12px; opacity: 0.85;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; font-weight: 500; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                    <p style="margin: 0; font-size: 12px; opacity: 0.85;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; font-weight: 500; opacity: 0.95;">tıklayınız</a></p>
                </div>
            </div>
        </div>';
    }

    // Premium Finans Template
    private function getPremiumFinansHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 50px 30px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Playfair Display\', \'Georgia\', serif;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -40px; right: -40px; width: 100px; height: 100px; background: rgba(217, 119, 6, 0.15); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -30px; left: -30px; width: 70px; height: 70px; background: rgba(217, 119, 6, 0.1); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2; max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-bottom: 10px; box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3); border: 3px solid rgba(217, 119, 6, 0.3);">
                        <span style="font-size: 28px;">👑</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 36px; font-weight: 900; text-shadow: 0 4px 12px rgba(0,0,0,0.4); letter-spacing: -0.5px; line-height: 1.2;">Borsanın Gündemi</h1>
                        <p style="margin: 8px 0 0 0; font-size: 14px; color: {{ secondary_color }}; font-weight: 500; letter-spacing: 0.5px;">Premium Finans Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 30px;">
                    <p style="margin: 0; font-size: 20px; color: {{ secondary_color }}; font-weight: 600;">Sayın <strong style="color: {{ text_color }};">#isim#</strong>,</p>
                    <p style="margin: 8px 0 0 0; font-size: 14px; color: {{ text_color }}; opacity: 0.9;">Premium finansal analizler ve elit yatırım önerileri</p>
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <span style="background: rgba(217, 119, 6, 0.2); padding: 8px 20px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(217, 119, 6, 0.3); backdrop-filter: blur(10px);">#tarih#</span>
                    <span style="background: rgba(217, 119, 6, 0.15); padding: 8px 20px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(217, 119, 6, 0.25); backdrop-filter: blur(10px);">💎 Premium Analiz</span>
                    <span style="background: rgba(217, 119, 6, 0.15); padding: 8px 20px; border-radius: 24px; font-size: 13px; font-weight: 600; border: 2px solid rgba(217, 119, 6, 0.25); backdrop-filter: blur(10px);">🏆 Elite Yatırım</span>
                </div>
            </div>
        </div>';
    }

    private function getPremiumFinansContent()
    {
        return '
        <div style="padding: 45px 30px; background: {{ background_color }}; font-family: \'Playfair Display\', \'Georgia\', serif;">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 35px;">
                    <h2 style="color: {{ primary_color }}; margin-bottom: 12px; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2;">💎 Premium Finansal Analiz</h2>
                    <p style="color: #78350f; font-size: 16px; margin: 0; font-weight: 400; opacity: 0.85;">Elit seviye finansal analizler ve premium yatırım stratejileri</p>
                </div>

                <div style="background: white; border-radius: 18px; padding: 35px; box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12); border: 3px solid {{ secondary_color }}; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }});"></div>
                    <div style="margin-top: 10px;">
                        {{ $newsletterContent }}
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 25px;">
                    <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2); border: 2px solid rgba(217, 119, 6, 0.2);">
                        <div style="font-size: 28px; margin-bottom: 10px;">💎</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Premium Analiz</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.5;">Elit seviye piyasa analizleri</p>
                    </div>
                    <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2); border: 2px solid rgba(217, 119, 6, 0.2);">
                        <div style="font-size: 28px; margin-bottom: 10px;">🏆</div>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">Elite Yatırım</h3>
                        <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.5;">Premium yatırım stratejileri</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getPremiumFinansFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 45px 30px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Playfair Display\', \'Georgia\', serif; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }});"></div>
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="margin-top: 10px; margin-bottom: 35px;">
                    <h3 style="color: {{ secondary_color }}; margin: 0 0 18px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">👑 Borsanın Gündemi</h3>
                    <p style="margin: 0 0 12px 0; opacity: 0.98; font-size: 15px; line-height: 1.7;">Sayın <strong>#isim#</strong>, premium finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 600;">tıklayınız</a></p>
                    <p style="margin: 0 0 18px 0; opacity: 0.9; font-size: 13px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
                </div>

                <div style="margin: 35px 0; padding: 30px; background: rgba(217, 119, 6, 0.12); border-radius: 18px; border: 2px solid rgba(217, 119, 6, 0.2); backdrop-filter: blur(10px);">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ secondary_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                    <div style="margin-bottom: 10px; opacity: 0.95; font-size: 14px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                    <div style="opacity: 0.95; font-size: 14px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
                </div>

                <div style="margin: 35px 0;">
                    <div style="font-weight: 800; margin-bottom: 18px; font-size: 16px; color: {{ secondary_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 54px; height: 54px; background: #1877f2; border-radius: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 6px 20px rgba(24, 119, 242, 0.35); border: 2px solid rgba(217, 119, 6, 0.2);">
                            <span style="color: white; font-weight: bold; font-size: 22px;">f</span>
                        </a>
                        <a href="#" style="width: 54px; height: 54px; background: #000000; border-radius: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35); border: 2px solid rgba(217, 119, 6, 0.2);">
                            <span style="color: white; font-weight: bold; font-size: 22px;">𝕏</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid rgba(217, 119, 6, 0.2);">
                    <p style="margin: 0 0 12px 0; font-size: 12px; opacity: 0.85;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                    <p style="margin: 0; font-size: 12px; opacity: 0.85;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
                </div>
            </div>
        </div>';
    }
}
