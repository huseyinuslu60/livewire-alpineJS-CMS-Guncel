<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateSeeder extends Seeder
{
    public function run()
    {
        // Mevcut template'leri temizle
        // NewsletterTemplate::truncate();

        $templates = [
            [
                'name' => 'Ultra Modern Pro',
                'slug' => 'ultra-modern-pro',
                'description' => '2024 yılının en modern tasarımı - Glassmorphism ve neumorphism efektleri',
                'header_html' => $this->getUltraModernProHeader(),
                'content_html' => $this->getUltraModernProContent(),
                'footer_html' => $this->getUltraModernProFooter(),
                'styles' => [
                    'primary_color' => '#6366f1',
                    'secondary_color' => '#8b5cf6',
                    'text_color' => '#ffffff',
                    'background_color' => '#f8fafc',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Financial Elite',
                'slug' => 'financial-elite',
                'description' => 'Elit finansal bülten - Bloomberg ve Reuters tarzı profesyonel tasarım',
                'header_html' => $this->getFinancialEliteHeader(),
                'content_html' => $this->getFinancialEliteContent(),
                'footer_html' => $this->getFinancialEliteFooter(),
                'styles' => [
                    'primary_color' => '#1e293b',
                    'secondary_color' => '#0f172a',
                    'text_color' => '#ffffff',
                    'background_color' => '#f1f5f9',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Corporate Luxury',
                'slug' => 'corporate-luxury',
                'description' => 'Lüks kurumsal tasarım - Altın ve siyah tonlarda premium görünüm',
                'header_html' => $this->getCorporateLuxuryHeader(),
                'content_html' => $this->getCorporateLuxuryContent(),
                'footer_html' => $this->getCorporateLuxuryFooter(),
                'styles' => [
                    'primary_color' => '#000000',
                    'secondary_color' => '#fbbf24',
                    'text_color' => '#ffffff',
                    'background_color' => '#fefce8',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Tech Innovation',
                'slug' => 'tech-innovation',
                'description' => 'Teknoloji odaklı modern tasarım - Neon efektler ve futuristik görünüm',
                'header_html' => $this->getTechInnovationHeader(),
                'content_html' => $this->getTechInnovationContent(),
                'footer_html' => $this->getTechInnovationFooter(),
                'styles' => [
                    'primary_color' => '#00d4aa',
                    'secondary_color' => '#00b4d8',
                    'text_color' => '#ffffff',
                    'background_color' => '#0a0a0a',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Minimalist Premium',
                'slug' => 'minimalist-premium',
                'description' => 'Premium minimal tasarım - Sade ama etkileyici, Apple tarzı',
                'header_html' => $this->getMinimalistPremiumHeader(),
                'content_html' => $this->getMinimalistPremiumContent(),
                'footer_html' => $this->getMinimalistPremiumFooter(),
                'styles' => [
                    'primary_color' => '#000000',
                    'secondary_color' => '#6b7280',
                    'text_color' => '#111827',
                    'background_color' => '#ffffff',
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Newsroom Professional',
                'slug' => 'newsroom-professional',
                'description' => 'Profesyonel haber odası tasarımı - CNN ve BBC tarzı',
                'header_html' => $this->getNewsroomProfessionalHeader(),
                'content_html' => $this->getNewsroomProfessionalContent(),
                'footer_html' => $this->getNewsroomProfessionalFooter(),
                'styles' => [
                    'primary_color' => '#dc2626',
                    'secondary_color' => '#ef4444',
                    'text_color' => '#ffffff',
                    'background_color' => '#fef2f2',
                ],
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Executive Dashboard',
                'slug' => 'executive-dashboard',
                'description' => 'Yönetici dashboard tasarımı - Veri odaklı ve analitik',
                'header_html' => $this->getExecutiveDashboardHeader(),
                'content_html' => $this->getExecutiveDashboardContent(),
                'footer_html' => $this->getExecutiveDashboardFooter(),
                'styles' => [
                    'primary_color' => '#059669',
                    'secondary_color' => '#10b981',
                    'text_color' => '#ffffff',
                    'background_color' => '#f0fdf4',
                ],
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Creative Modern',
                'slug' => 'creative-modern',
                'description' => 'Yaratıcı modern tasarım - Sanatsal ve dinamik',
                'header_html' => $this->getCreativeModernHeader(),
                'content_html' => $this->getCreativeModernContent(),
                'footer_html' => $this->getCreativeModernFooter(),
                'styles' => [
                    'primary_color' => '#ec4899',
                    'secondary_color' => '#8b5cf6',
                    'text_color' => '#ffffff',
                    'background_color' => '#fef3c7',
                ],
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($templates as $template) {
            NewsletterTemplate::create($template);
        }
    }

    // Ultra Modern Pro Template
    private function getUltraModernProHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 80px 40px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="position: absolute; top: -100px; right: -100px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; backdrop-filter: blur(20px);"></div>
            <div style="position: absolute; bottom: -80px; left: -80px; width: 160px; height: 160px; background: rgba(255,255,255,0.08); border-radius: 50%; backdrop-filter: blur(15px);"></div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 40px; flex-wrap: wrap;">
                    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.15); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin-right: 25px; margin-bottom: 15px; backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
                        <span style="font-size: 32px; font-weight: bold;">📊</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 48px; font-weight: 800; text-shadow: 0 4px 8px rgba(0,0,0,0.3); letter-spacing: -1px; line-height: 1.2;">Borsanın Gündemi</h1>
                        <p style="margin: 10px 0 0 0; font-size: 20px; opacity: 0.9; font-weight: 400;">Ultra Modern Finans Bülteni</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 40px;">
                    <p style="margin: 0; font-size: 24px; opacity: 0.95; font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 10px 0 0 0; font-size: 18px; opacity: 0.8;">Finans dünyasından en güncel haberler ve profesyonel analizler</p>
                </div>

                <div style="margin-top: 40px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 30px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.15); padding: 12px 24px; border-radius: 30px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">📈 Piyasa Analizi</span>
                    <span style="background: rgba(255,255,255,0.15); padding: 12px 24px; border-radius: 30px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">💼 Yatırım Önerileri</span>
                </div>
            </div>
        </div>';
    }

    private function getUltraModernProContent()
    {
        return '
        <div style="padding: 60px 40px; background: {{ background_color }}; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="text-align: center; margin-bottom: 50px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 20px; font-size: 36px; font-weight: 800; letter-spacing: -0.5px;">📊 Piyasa Güncellemeleri</h2>
                <p style="color: {{ text_color }}; font-size: 20px; margin: 0; font-weight: 400; opacity: 0.8;">Finans dünyasından en güncel haberler ve profesyonel analizler</p>
            </div>

            <div style="background: rgba(255, 255, 255, 0.95); border-radius: 24px; padding: 50px; backdrop-filter: blur(20px); border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);">
                {{ $newsletterContent }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-top: 40px;">
                <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);">
                    <div style="font-size: 32px; margin-bottom: 15px;">📈</div>
                    <h3 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 700;">Piyasa Performansı</h3>
                    <p style="margin: 0; font-size: 15px; opacity: 0.9;">Günlük piyasa analizleri ve trendler</p>
                </div>
                <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);">
                    <div style="font-size: 32px; margin-bottom: 15px;">💼</div>
                    <h3 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 700;">Yatırım Stratejileri</h3>
                    <p style="margin: 0; font-size: 15px; opacity: 0.9;">Uzman yatırım önerileri</p>
                </div>
            </div>
        </div>';
    }

    private function getUltraModernProFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 60px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="margin-bottom: 50px;">
                <h3 style="color: {{ text_color }}; margin: 0 0 25px 0; font-size: 28px; font-weight: 800;">📊 Borsanın Gündemi</h3>
                <p style="margin: 0 0 20px 0; opacity: 0.95; font-size: 18px; line-height: 1.6;">Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.9;">tıklayınız</a></p>
                <p style="margin: 0 0 25px 0; opacity: 0.8; font-size: 16px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 50px 0; padding: 40px; background: rgba(255,255,255,0.08); border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-weight: 800; margin-bottom: 25px; font-size: 20px; color: {{ text_color }};">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 15px; opacity: 0.9; font-size: 16px;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 15px; opacity: 0.9; font-size: 16px;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 15px; opacity: 0.9; font-size: 16px;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="opacity: 0.9; font-size: 16px;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 50px 0;">
                <div style="font-weight: 800; margin-bottom: 25px; font-size: 20px; color: {{ text_color }};">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 25px;">
                    <a href="#" style="width: 70px; height: 70px; background: #1877f2; border-radius: 20px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 6px 20px rgba(24, 119, 242, 0.3);">
                        <span style="color: white; font-weight: bold; font-size: 28px;">f</span>
                    </a>
                    <a href="#" style="width: 70px; height: 70px; background: #000000; border-radius: 20px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);">
                        <span style="color: white; font-weight: bold; font-size: 28px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 50px; padding-top: 40px; border-top: 1px solid rgba(255,255,255,0.2);">
                <p style="margin: 0 0 20px 0; font-size: 14px; opacity: 0.7;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.9;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 14px; opacity: 0.7;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.9;">tıklayınız</a></p>
            </div>
        </div>';
    }

    // Financial Elite Template
    private function getFinancialEliteHeader()
    {
        return '
        <div style="background: {{ primary_color }}; padding: 0; color: {{ text_color }}; font-family: \'Georgia\', \'Times New Roman\', serif;">
            <div style="background: {{ secondary_color }}; padding: 10px 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 2px solid {{ primary_color }};">
                BORSANIN GÜNDEMİ | FINANCIAL BRIEFING
            </div>

            <div style="padding: 40px 20px; border-bottom: 4px solid {{ secondary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
                    <div>
                        <h1 style="margin: 0; font-size: 32px; font-weight: 900; color: {{ text_color }}; letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 8px 0 0 0; font-size: 14px; color: {{ text_color }}; font-weight: 400; opacity: 0.85;">Professional Financial Newsletter</p>
                    </div>
                    <div style="text-align: right; color: {{ text_color }}; font-size: 12px; opacity: 0.9; margin-top: 15px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>

                <div style="background: {{ secondary_color }}; padding: 12px 18px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: bold; flex-wrap: wrap; gap: 10px;">
                    <span>📈 BIST 100: 8,245.67 (+1.2%)</span>
                    <span>💱 USD/TRY: 32.45 (+0.8%)</span>
                    <span>⏰ Son Güncelleme: 15:30</span>
                </div>
            </div>
        </div>';
    }

    private function getFinancialEliteContent()
    {
        return '
        <div style="background: {{ background_color }}; padding: 0; font-family: \'Georgia\', \'Times New Roman\', serif;">
            <div style="background: white; margin: 20px; border: 2px solid {{ primary_color }}; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
                <div style="background: {{ primary_color }}; color: {{ text_color }}; padding: 14px 22px; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    📊 MARKET OVERVIEW
                </div>

                <div style="padding: 25px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: {{ background_color }}; border-bottom: 3px solid {{ primary_color }};">
                                <th style="padding: 12px; text-align: left; font-weight: bold; color: {{ primary_color }}; font-size: 12px; text-transform: uppercase;">Index</th>
                                <th style="padding: 12px; text-align: right; font-weight: bold; color: {{ primary_color }}; font-size: 12px; text-transform: uppercase;">Value</th>
                                <th style="padding: 12px; text-align: right; font-weight: bold; color: {{ primary_color }}; font-size: 12px; text-transform: uppercase;">Change</th>
                                <th style="padding: 12px; text-align: right; font-weight: bold; color: {{ primary_color }}; font-size: 12px; text-transform: uppercase;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px; font-weight: 600; color: {{ primary_color }};">BIST 100</td>
                                <td style="padding: 12px; text-align: right; font-weight: 600; color: {{ primary_color }};">8,245.67</td>
                                <td style="padding: 12px; text-align: right; color: #059669; font-weight: 600;">+98.45</td>
                                <td style="padding: 12px; text-align: right; color: #059669; font-weight: 600;">+1.21%</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px; font-weight: 600; color: {{ primary_color }};">BIST 30</td>
                                <td style="padding: 12px; text-align: right; font-weight: 600; color: {{ primary_color }};">1,456.23</td>
                                <td style="padding: 12px; text-align: right; color: #059669; font-weight: 600;">+23.12</td>
                                <td style="padding: 12px; text-align: right; color: #059669; font-weight: 600;">+1.61%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="background: white; margin: 20px; border: 2px solid {{ primary_color }}; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
                <div style="background: {{ primary_color }}; color: {{ text_color }}; padding: 14px 22px; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    📰 MARKET NEWS
                </div>
                <div style="padding: 25px; color: {{ text_color }}; line-height: 1.7;">
                    {{ $newsletterContent }}
                </div>
            </div>
        </div>';
    }

    private function getFinancialEliteFooter()
    {
        return '
        <div style="background: {{ primary_color }}; color: {{ text_color }}; font-family: \'Georgia\', \'Times New Roman\', serif; font-size: 12px;">
            <div style="background: {{ secondary_color }}; padding: 18px 22px; border-bottom: 2px solid {{ primary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: {{ text_color }};">BORSANIN GÜNDEMİ</h3>
                        <p style="margin: 6px 0 0 0; font-size: 12px; color: {{ text_color }}; opacity: 0.85;">Professional Financial Newsletter</p>
                    </div>
                    <div style="text-align: right; font-size: 12px; color: {{ text_color }}; opacity: 0.9; margin-top: 10px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>
            </div>

            <div style="padding: 25px; background: {{ primary_color }};">
                <p style="margin: 0 0 18px 0; color: {{ text_color }}; line-height: 1.6; font-size: 13px; opacity: 0.95;">
                    Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik.
                    Daha fazla analiz ve güncel veriler için <a href="#" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.9;">web sitemizi ziyaret edin</a>.
                </p>
                <p style="margin: 0 0 22px 0; color: {{ text_color }}; font-size: 12px; opacity: 0.85;">
                    Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.
                </p>
            </div>

            <div style="background: {{ secondary_color }}; padding: 25px; border-top: 2px solid {{ primary_color }};">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK A.Ş.</h4>
                        <div style="font-size: 12px; color: {{ text_color }}; line-height: 1.6; opacity: 0.9;">
                            <div>📍 Ergenekon Mah. Cumhuriyet Cad.</div>
                            <div>📍 Efser Han No: 181 Kat: 8</div>
                            <div>📍 Harbiye - Şişli - İstanbul</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">İLETİŞİM</h4>
                        <div style="font-size: 12px; color: {{ text_color }}; line-height: 1.6; opacity: 0.9;">
                            <div>📞 Tel: 0 212 294 11 69</div>
                            <div>📞 Mobil: 0 530 849 88 48</div>
                            <div>📠 Faks: 0 212 238 72 07</div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin: 25px 0;">
                    <div style="font-size: 13px; font-weight: bold; color: {{ text_color }}; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">BİZİ TAKİP EDİN</div>
                    <div style="display: flex; justify-content: center; gap: 18px;">
                        <a href="#" style="width: 42px; height: 42px; background: #1877f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(24, 119, 242, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 16px;">f</span>
                        </a>
                        <a href="#" style="width: 42px; height: 42px; background: #000000; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);">
                            <span style="color: white; font-weight: bold; font-size: 16px;">𝕏</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: {{ primary_color }}; padding: 18px 22px; border-top: 2px solid {{ secondary_color }}; text-align: center;">
                <p style="margin: 0 0 12px 0; font-size: 11px; color: {{ text_color }}; opacity: 0.85;">
                    Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.
                </p>
                <p style="margin: 0; font-size: 11px; color: {{ text_color }}; opacity: 0.85;">
                    Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95;">tıklayınız</a>
                </p>
            </div>
        </div>';
    }

    // Corporate Luxury Template
    private function getCorporateLuxuryHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 70px 40px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Playfair Display\', \'Georgia\', serif;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -50px; right: -50px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -40px; left: -40px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 45px; flex-wrap: wrap;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 25px; margin-bottom: 15px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); border: 3px solid rgba(255,255,255,0.2);">
                        <span style="font-size: 32px; font-weight: bold;">👑</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 44px; font-weight: 900; text-shadow: 0 4px 12px rgba(0,0,0,0.4); letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 12px 0 0 0; font-size: 18px; color: {{ secondary_color }}; font-weight: 500; letter-spacing: 0.5px;">Luxury Financial Newsletter</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 45px;">
                    <p style="margin: 0; font-size: 24px; color: {{ secondary_color }}; font-weight: 600;">Sayın <strong style="color: {{ text_color }};">#isim#</strong>,</p>
                    <p style="margin: 12px 0 0 0; font-size: 17px; color: {{ text_color }}; opacity: 0.9;">Premium finansal analizler ve elit yatırım önerileri</p>
                </div>

                <div style="margin-top: 45px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.15); padding: 14px 28px; border-radius: 30px; font-size: 15px; font-weight: 600; border: 2px solid rgba(255,255,255,0.25); backdrop-filter: blur(10px);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.12); padding: 14px 28px; border-radius: 30px; font-size: 15px; font-weight: 600; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">💎 Premium Analiz</span>
                    <span style="background: rgba(255,255,255,0.12); padding: 14px 28px; border-radius: 30px; font-size: 15px; font-weight: 600; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">🏆 Elite Yatırım</span>
                </div>
            </div>
        </div>';
    }

    private function getCorporateLuxuryContent()
    {
        return '
        <div style="padding: 70px 40px; background: {{ background_color }}; font-family: \'Playfair Display\', \'Georgia\', serif;">
            <div style="text-align: center; margin-bottom: 55px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 22px; font-size: 36px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2;">💎 Premium Finansal Analiz</h2>
                <p style="color: {{ text_color }}; font-size: 19px; margin: 0; font-weight: 400; opacity: 0.85;">Elit seviye finansal analizler ve premium yatırım stratejileri</p>
            </div>

            <div style="background: white; border-radius: 24px; padding: 55px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12); border: 3px solid {{ secondary_color }}; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }});"></div>
                <div style="margin-top: 10px;">
                    {{ $newsletterContent }}
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 50px;">
                <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 36px; margin-bottom: 18px;">💎</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Premium Analiz</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Elit seviye piyasa analizleri ve trend raporları</p>
                </div>
                <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 36px; margin-bottom: 18px;">🏆</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Elite Yatırım</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Premium yatırım stratejileri ve portföy yönetimi</p>
                </div>
            </div>
        </div>';
    }

    private function getCorporateLuxuryFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 70px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Playfair Display\', \'Georgia\', serif; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }});"></div>
            <div style="margin-top: 10px; margin-bottom: 55px;">
                <h3 style="color: {{ secondary_color }}; margin: 0 0 28px 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px;">👑 Borsanın Gündemi</h3>
                <p style="margin: 0 0 22px 0; opacity: 0.95; font-size: 19px; line-height: 1.7;">Sayın <strong>#isim#</strong>, premium finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 600;">tıklayınız</a></p>
                <p style="margin: 0 0 28px 0; opacity: 0.85; font-size: 17px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 55px 0; padding: 45px; background: rgba(255,255,255,0.08); border-radius: 24px; border: 2px solid rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ secondary_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="opacity: 0.9; font-size: 17px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 55px 0;">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ secondary_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 28px;">
                    <a href="#" style="width: 75px; height: 75px; background: #1877f2; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 8px 24px rgba(24, 119, 242, 0.35); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">f</span>
                    </a>
                    <a href="#" style="width: 75px; height: 75px; background: #000000; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 55px; padding-top: 45px; border-top: 2px solid rgba(255,255,255,0.15);">
                <p style="margin: 0 0 22px 0; font-size: 15px; opacity: 0.8;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 15px; opacity: 0.8;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
            </div>
        </div>';
    }

    // Tech Innovation Template
    private function getTechInnovationHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 85px 40px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Roboto Mono\', \'Courier New\', monospace;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -100px; right: -100px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(255,255,255,0.08), transparent); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -80px; left: -80px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(255,255,255,0.06), transparent); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 45px; flex-wrap: wrap;">
                    <div style="width: 85px; height: 85px; background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 28px; margin-bottom: 15px; box-shadow: 0 0 40px rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.15);">
                        <span style="font-size: 36px; font-weight: bold;">⚡</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 50px; font-weight: 900; text-shadow: 0 0 25px rgba(255,255,255,0.3); letter-spacing: -1px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 12px 0 0 0; font-size: 21px; color: {{ secondary_color }}; font-weight: 400; letter-spacing: 0.5px;">Tech Innovation Newsletter</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 45px;">
                    <p style="margin: 0; font-size: 26px; color: {{ secondary_color }}; font-weight: 500;">Merhaba <strong style="color: {{ text_color }};">#isim#</strong>,</p>
                    <p style="margin: 12px 0 0 0; font-size: 19px; color: {{ text_color }}; opacity: 0.85;">Teknoloji odaklı finansal analizler ve futuristik inovasyon</p>
                </div>

                <div style="margin-top: 45px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.12); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.1); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; border: 2px solid rgba(255,255,255,0.18); backdrop-filter: blur(10px);">🚀 Tech Analysis</span>
                    <span style="background: rgba(255,255,255,0.1); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; border: 2px solid rgba(255,255,255,0.18); backdrop-filter: blur(10px);">💻 Innovation</span>
                </div>
            </div>
        </div>';
    }

    private function getTechInnovationContent()
    {
        return '
        <div style="padding: 70px 40px; background: {{ background_color }}; color: {{ text_color }}; font-family: \'Roboto Mono\', \'Courier New\', monospace;">
            <div style="text-align: center; margin-bottom: 55px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 22px; font-size: 38px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2;">⚡ Tech Innovation</h2>
                <p style="color: {{ text_color }}; font-size: 21px; margin: 0; font-weight: 400; opacity: 0.85;">Teknoloji odaklı finansal analizler ve futuristik inovasyon trendleri</p>
            </div>

            <div style="background: rgba(255,255,255,0.05); border-radius: 24px; padding: 55px; border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 0 40px rgba(0, 0, 0, 0.15); backdrop-filter: blur(10px);">
                {{ $newsletterContent }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 50px;">
                <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 0 35px rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 38px; margin-bottom: 18px;">🚀</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Tech Analysis</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Teknoloji odaklı piyasa analizleri ve AI destekli raporlar</p>
                </div>
                <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 0 35px rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 38px; margin-bottom: 18px;">💻</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Innovation</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Fintech inovasyonları ve blockchain teknolojileri</p>
                </div>
            </div>
        </div>';
    }

    private function getTechInnovationFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 70px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Roboto Mono\', \'Courier New\', monospace; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="margin-top: 10px; margin-bottom: 55px;">
                <h3 style="color: {{ secondary_color }}; margin: 0 0 28px 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px; text-shadow: 0 0 20px rgba(255,255,255,0.2);">⚡ Borsanın Gündemi</h3>
                <p style="margin: 0 0 22px 0; opacity: 0.95; font-size: 19px; line-height: 1.7;">Sayın <strong>#isim#</strong>, teknoloji odaklı finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 600;">tıklayınız</a></p>
                <p style="margin: 0 0 28px 0; opacity: 0.85; font-size: 17px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 55px 0; padding: 45px; background: rgba(255,255,255,0.08); border-radius: 24px; border: 2px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ secondary_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="opacity: 0.9; font-size: 17px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 55px 0;">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ secondary_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 28px;">
                    <a href="#" style="width: 75px; height: 75px; background: #1877f2; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 0 25px rgba(24, 119, 242, 0.4); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">f</span>
                    </a>
                    <a href="#" style="width: 75px; height: 75px; background: #000000; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 0 25px rgba(0, 0, 0, 0.4); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 55px; padding-top: 45px; border-top: 2px solid rgba(255,255,255,0.12);">
                <p style="margin: 0 0 22px 0; font-size: 15px; opacity: 0.8;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 15px; opacity: 0.8;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ secondary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
            </div>
        </div>';
    }

    // Minimalist Premium Template
    private function getMinimalistPremiumHeader()
    {
        return '
        <div style="background: {{ background_color }}; padding: 85px 40px; text-align: center; color: {{ text_color }}; position: relative; border-bottom: 2px solid {{ primary_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 45px; flex-wrap: wrap;">
                    <div style="width: 70px; height: 70px; background: {{ primary_color }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 28px; margin-bottom: 15px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);">
                        <span style="font-size: 28px; font-weight: bold; color: {{ background_color }};">📊</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 44px; font-weight: 300; color: {{ primary_color }}; letter-spacing: -1px; line-height: 1.2;">Borsanın Gündemi</h1>
                        <p style="margin: 12px 0 0 0; font-size: 17px; color: {{ text_color }}; font-weight: 400; opacity: 0.7;">Minimalist Financial Newsletter</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 45px;">
                    <p style="margin: 0; font-size: 22px; color: {{ primary_color }}; font-weight: 400;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 12px 0 0 0; font-size: 17px; color: {{ text_color }}; opacity: 0.75;">Sade ve etkili finansal analizler</p>
                </div>

                <div style="margin-top: 45px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap;">
                    <span style="background: {{ primary_color }}; color: {{ background_color }}; padding: 10px 20px; border-radius: 24px; font-size: 15px; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">#tarih#</span>
                    <span style="background: {{ text_color }}; color: {{ background_color }}; padding: 10px 20px; border-radius: 24px; font-size: 15px; font-weight: 500; opacity: 0.1; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">📈 Market Analysis</span>
                    <span style="background: {{ text_color }}; color: {{ background_color }}; padding: 10px 20px; border-radius: 24px; font-size: 15px; font-weight: 500; opacity: 0.1; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">💼 Investment</span>
                </div>
            </div>
        </div>';
    }

    private function getMinimalistPremiumContent()
    {
        return '
        <div style="padding: 70px 40px; background: {{ background_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="text-align: center; margin-bottom: 55px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 22px; font-size: 34px; font-weight: 300; letter-spacing: -0.5px; line-height: 1.2;">📊 Market Analysis</h2>
                <p style="color: {{ text_color }}; font-size: 19px; margin: 0; font-weight: 400; opacity: 0.75;">Sade ve etkili finansal analizler</p>
            </div>

            <div style="background: white; border-radius: 16px; padding: 55px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06); border: 1px solid rgba(0, 0, 0, 0.08);">
                {{ $newsletterContent }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 50px;">
                <div style="background: {{ primary_color }}; color: {{ background_color }}; padding: 35px; border-radius: 16px; text-align: center; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);">
                    <div style="font-size: 36px; margin-bottom: 18px;">📈</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 600; color: {{ background_color }};">Market Analysis</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; color: {{ background_color }}; line-height: 1.5;">Sade piyasa analizleri ve trend raporları</p>
                </div>
                <div style="background: {{ text_color }}; color: {{ background_color }}; padding: 35px; border-radius: 16px; text-align: center; border: 2px solid {{ primary_color }}; opacity: 0.05; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);">
                    <div style="font-size: 36px; margin-bottom: 18px;">💼</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 600;">Investment</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.8; line-height: 1.5;">Minimal yatırım stratejileri ve portföy yönetimi</p>
                </div>
            </div>
        </div>';
    }

    private function getMinimalistPremiumFooter()
    {
        return '
        <div style="background: {{ background_color }}; padding: 70px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; border-top: 2px solid {{ primary_color }}; font-family: \'SF Pro Display\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif;">
            <div style="margin-bottom: 55px;">
                <h3 style="color: {{ primary_color }}; margin: 0 0 28px 0; font-size: 28px; font-weight: 300; letter-spacing: -0.5px;">📊 Borsanın Gündemi</h3>
                <p style="margin: 0 0 22px 0; font-size: 18px; line-height: 1.7; color: {{ text_color }}; opacity: 0.85;">Sayın <strong>#isim#</strong>, sade ve etkili finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
                <p style="margin: 0 0 28px 0; font-size: 16px; color: {{ text_color }}; opacity: 0.75;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 55px 0; padding: 40px; background: white; border-radius: 16px; border: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);">
                <div style="font-weight: 600; margin-bottom: 24px; font-size: 18px; color: {{ primary_color }}; letter-spacing: 0.3px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 12px; font-size: 16px; color: {{ text_color }}; opacity: 0.8; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 12px; font-size: 16px; color: {{ text_color }}; opacity: 0.8; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 12px; font-size: 16px; color: {{ text_color }}; opacity: 0.8; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="font-size: 16px; color: {{ text_color }}; opacity: 0.8; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 55px 0;">
                <div style="font-weight: 600; margin-bottom: 24px; font-size: 18px; color: {{ primary_color }}; letter-spacing: 0.3px;">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 24px;">
                    <a href="#" style="width: 58px; height: 58px; background: #1877f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(24, 119, 242, 0.25);">
                        <span style="color: white; font-weight: bold; font-size: 22px;">f</span>
                    </a>
                    <a href="#" style="width: 58px; height: 58px; background: #000000; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);">
                        <span style="color: white; font-weight: bold; font-size: 22px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 55px; padding-top: 40px; border-top: 1px solid rgba(0, 0, 0, 0.08);">
                <p style="margin: 0 0 18px 0; font-size: 13px; color: {{ text_color }}; opacity: 0.7;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 13px; color: {{ text_color }}; opacity: 0.7;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ primary_color }}; text-decoration: underline; font-weight: 500;">tıklayınız</a></p>
            </div>
        </div>';
    }

    // Newsroom Professional Template
    private function getNewsroomProfessionalHeader()
    {
        return '
        <div style="background: {{ primary_color }}; padding: 0; color: {{ text_color }}; font-family: \'Roboto\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="background: {{ secondary_color }}; padding: 12px 25px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 3px solid {{ primary_color }};">
                BREAKING NEWS | BORSANIN GÜNDEMİ
            </div>

            <div style="padding: 45px 25px; border-bottom: 5px solid {{ secondary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap;">
                    <div>
                        <h1 style="margin: 0; font-size: 36px; font-weight: 900; color: {{ text_color }}; letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 8px 0 0 0; font-size: 15px; color: {{ text_color }}; font-weight: 400; opacity: 0.9;">Professional Newsroom Newsletter</p>
                    </div>
                    <div style="text-align: right; color: {{ text_color }}; font-size: 13px; opacity: 0.9; margin-top: 15px;">
                        <div style="font-weight: bold; margin-bottom: 6px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>

                <div style="background: {{ secondary_color }}; padding: 14px 20px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: bold; flex-wrap: wrap; gap: 12px;">
                    <span>📈 BIST 100: 8,245.67 (+1.2%)</span>
                    <span>💱 USD/TRY: 32.45 (+0.8%)</span>
                    <span>⏰ Son Güncelleme: 15:30</span>
                </div>
            </div>
        </div>';
    }

    private function getNewsroomProfessionalContent()
    {
        return '
        <div style="background: {{ background_color }}; padding: 0; font-family: \'Roboto\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="background: white; margin: 25px; border: 3px solid {{ primary_color }}; border-radius: 6px; overflow: hidden; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);">
                <div style="background: {{ primary_color }}; color: {{ text_color }}; padding: 16px 25px; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    📰 FINANCIAL NEWS
                </div>
                <div style="padding: 30px; color: {{ text_color }}; line-height: 1.8;">
                    {{ $newsletterContent }}
                </div>
            </div>
        </div>';
    }

    private function getNewsroomProfessionalFooter()
    {
        return '
        <div style="background: {{ primary_color }}; color: {{ text_color }}; font-family: \'Roboto\', \'Helvetica Neue\', Arial, sans-serif; font-size: 12px;">
            <div style="background: {{ secondary_color }}; padding: 20px 25px; border-bottom: 3px solid {{ primary_color }};">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <h3 style="margin: 0; font-size: 20px; font-weight: bold; color: {{ text_color }};">BORSANIN GÜNDEMİ</h3>
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: {{ text_color }}; opacity: 0.9;">Professional Newsroom Newsletter</p>
                    </div>
                    <div style="text-align: right; font-size: 13px; color: {{ text_color }}; opacity: 0.9; margin-top: 10px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">#tarih#</div>
                        <div>#isim#</div>
                    </div>
                </div>
            </div>

            <div style="padding: 28px; background: {{ primary_color }};">
                <p style="margin: 0 0 20px 0; color: {{ text_color }}; line-height: 1.7; font-size: 14px; opacity: 0.95;">
                    Sayın <strong>#isim#</strong>, günün öne çıkan finansal haberlerinden bazılarını sizin için derledik.
                    Daha fazla analiz ve güncel veriler için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">web sitemizi ziyaret edin</a>.
                </p>
                <p style="margin: 0 0 25px 0; color: {{ text_color }}; font-size: 13px; opacity: 0.9;">
                    Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.
                </p>
            </div>

            <div style="background: {{ secondary_color }}; padding: 28px; border-top: 3px solid {{ primary_color }};">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-bottom: 28px;">
                    <div>
                        <h4 style="margin: 0 0 14px 0; font-size: 15px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK A.Ş.</h4>
                        <div style="font-size: 13px; color: {{ text_color }}; line-height: 1.7; opacity: 0.9;">
                            <div>📍 Ergenekon Mah. Cumhuriyet Cad.</div>
                            <div>📍 Efser Han No: 181 Kat: 8</div>
                            <div>📍 Harbiye - Şişli - İstanbul</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 14px 0; font-size: 15px; font-weight: bold; color: {{ text_color }}; text-transform: uppercase; letter-spacing: 0.5px;">İLETİŞİM</h4>
                        <div style="font-size: 13px; color: {{ text_color }}; line-height: 1.7; opacity: 0.9;">
                            <div>📞 Tel: 0 212 294 11 69</div>
                            <div>📞 Mobil: 0 530 849 88 48</div>
                            <div>📠 Faks: 0 212 238 72 07</div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin: 28px 0;">
                    <div style="font-size: 14px; font-weight: bold; color: {{ text_color }}; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">BİZİ TAKİP EDİN</div>
                    <div style="display: flex; justify-content: center; gap: 20px;">
                        <a href="#" style="width: 45px; height: 45px; background: #1877f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);">
                            <span style="color: white; font-weight: bold; font-size: 18px;">f</span>
                        </a>
                        <a href="#" style="width: 45px; height: 45px; background: #000000; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">
                            <span style="color: white; font-weight: bold; font-size: 18px;">𝕏</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: {{ primary_color }}; padding: 18px 25px; border-top: 3px solid {{ secondary_color }}; text-align: center;">
                <p style="margin: 0 0 14px 0; font-size: 12px; color: {{ text_color }}; opacity: 0.9;">
                    Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.
                </p>
                <p style="margin: 0; font-size: 12px; color: {{ text_color }}; opacity: 0.9;">
                    Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600;">tıklayınız</a>
                </p>
            </div>
        </div>';
    }

    // Executive Dashboard Template
    private function getExecutiveDashboardHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 75px 40px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Montserrat\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -80px; right: -80px; width: 180px; height: 180px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -60px; left: -60px; width: 140px; height: 140px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 42px; flex-wrap: wrap;">
                    <div style="width: 75px; height: 75px; background: rgba(255,255,255,0.12); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin-right: 26px; margin-bottom: 15px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.18); box-shadow: 0 8px 32px rgba(0,0,0,0.15);">
                        <span style="font-size: 30px; font-weight: bold;">📊</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 46px; font-weight: 800; text-shadow: 0 4px 10px rgba(0,0,0,0.25); letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 11px 0 0 0; font-size: 19px; opacity: 0.9; font-weight: 500;">Executive Dashboard Newsletter</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 42px;">
                    <p style="margin: 0; font-size: 25px; opacity: 0.96; font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 11px 0 0 0; font-size: 19px; opacity: 0.85;">Yönetici seviyesi finansal analizler ve stratejik öngörüler</p>
                </div>

                <div style="margin-top: 42px; display: flex; justify-content: center; gap: 17px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.18); padding: 13px 26px; border-radius: 28px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.28);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.14); padding: 13px 26px; border-radius: 28px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.24);">📊 Executive Report</span>
                    <span style="background: rgba(255,255,255,0.14); padding: 13px 26px; border-radius: 28px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.24);">💼 Strategic Analysis</span>
                </div>
            </div>
        </div>';
    }

    private function getExecutiveDashboardContent()
    {
        return '
        <div style="padding: 68px 40px; background: {{ background_color }}; font-family: \'Montserrat\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 54px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 22px; font-size: 37px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2;">📊 Executive Dashboard</h2>
                <p style="color: {{ text_color }}; font-size: 20px; margin: 0; font-weight: 400; opacity: 0.85;">Yönetici seviyesi finansal analizler ve stratejik öngörüler</p>
            </div>

            <div style="background: rgba(255, 255, 255, 0.98); border-radius: 22px; padding: 54px; backdrop-filter: blur(20px); border: 2px solid rgba(0, 0, 0, 0.06); box-shadow: 0 22px 55px rgba(0, 0, 0, 0.09);">
                {{ $newsletterContent }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(318px, 1fr)); gap: 28px; margin-top: 48px;">
                <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 34px; border-radius: 18px; text-align: center; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.16);">
                    <div style="font-size: 34px; margin-bottom: 17px;">📊</div>
                    <h3 style="margin: 0 0 17px 0; font-size: 21px; font-weight: 700;">Executive Report</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Yönetici seviyesi piyasa raporları ve analizler</p>
                </div>
                <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 34px; border-radius: 18px; text-align: center; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.16);">
                    <div style="font-size: 34px; margin-bottom: 17px;">💼</div>
                    <h3 style="margin: 0 0 17px 0; font-size: 21px; font-weight: 700;">Strategic Analysis</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Stratejik yatırım analizleri ve portföy önerileri</p>
                </div>
            </div>
        </div>';
    }

    private function getExecutiveDashboardFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 68px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Montserrat\', \'Helvetica Neue\', Arial, sans-serif; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="margin-top: 10px; margin-bottom: 54px;">
                <h3 style="color: {{ text_color }}; margin: 0 0 27px 0; font-size: 30px; font-weight: 800; letter-spacing: -0.5px;">📊 Borsanın Gündemi</h3>
                <p style="margin: 0 0 21px 0; opacity: 0.96; font-size: 19px; line-height: 1.7;">Sayın <strong>#isim#</strong>, yönetici seviyesi finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">tıklayınız</a></p>
                <p style="margin: 0 0 27px 0; opacity: 0.85; font-size: 17px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 54px 0; padding: 44px; background: rgba(255,255,255,0.08); border-radius: 22px; border: 2px solid rgba(255,255,255,0.14); backdrop-filter: blur(10px);">
                <div style="font-weight: 800; margin-bottom: 27px; font-size: 21px; color: {{ text_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 17px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 17px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 17px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="opacity: 0.9; font-size: 17px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 54px 0;">
                <div style="font-weight: 800; margin-bottom: 27px; font-size: 21px; color: {{ text_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 27px;">
                    <a href="#" style="width: 73px; height: 73px; background: #1877f2; border-radius: 21px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 7px 23px rgba(24, 119, 242, 0.32); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 29px;">f</span>
                    </a>
                    <a href="#" style="width: 73px; height: 73px; background: #000000; border-radius: 21px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 7px 23px rgba(0, 0, 0, 0.32); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 29px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 54px; padding-top: 44px; border-top: 2px solid rgba(255,255,255,0.14);">
                <p style="margin: 0 0 21px 0; font-size: 15px; opacity: 0.8;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95; font-weight: 500;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 15px; opacity: 0.8;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; opacity: 0.95; font-weight: 500;">tıklayınız</a></p>
            </div>
        </div>';
    }

    // Creative Modern Template
    private function getCreativeModernHeader()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 82px 40px; text-align: center; color: {{ text_color }}; position: relative; overflow: hidden; font-family: \'Poppins\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="position: absolute; top: -90px; right: -90px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1), transparent); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -70px; left: -70px; width: 170px; height: 170px; background: radial-gradient(circle, rgba(255,255,255,0.08), transparent); border-radius: 50%;"></div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 44px; flex-wrap: wrap;">
                    <div style="width: 82px; height: 82px; background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 27px; margin-bottom: 15px; box-shadow: 0 10px 35px rgba(255,255,255,0.25); border: 3px solid rgba(255,255,255,0.2);">
                        <span style="font-size: 34px; font-weight: bold;">✨</span>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 48px; font-weight: 900; text-shadow: 0 4px 12px rgba(0,0,0,0.3); letter-spacing: -0.5px; line-height: 1.2;">BORSANIN GÜNDEMİ</h1>
                        <p style="margin: 12px 0 0 0; font-size: 20px; opacity: 0.9; font-weight: 500; letter-spacing: 0.3px;">Creative Modern Newsletter</p>
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 44px;">
                    <p style="margin: 0; font-size: 25px; opacity: 0.96; font-weight: 500;">Merhaba <strong>#isim#</strong>,</p>
                    <p style="margin: 12px 0 0 0; font-size: 19px; opacity: 0.85;">Yaratıcı ve modern finansal analizler</p>
                </div>

                <div style="margin-top: 44px; display: flex; justify-content: center; gap: 18px; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3);">#tarih#</span>
                    <span style="background: rgba(255,255,255,0.16); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.26);">🎨 Creative Analysis</span>
                    <span style="background: rgba(255,255,255,0.16); padding: 14px 28px; border-radius: 32px; font-size: 16px; font-weight: 600; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.26);">💡 Modern Insights</span>
                </div>
            </div>
        </div>';
    }

    private function getCreativeModernContent()
    {
        return '
        <div style="padding: 70px 40px; background: {{ background_color }}; font-family: \'Poppins\', \'Helvetica Neue\', Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 55px;">
                <h2 style="color: {{ primary_color }}; margin-bottom: 22px; font-size: 38px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2;">✨ Creative Modern</h2>
                <p style="color: {{ text_color }}; font-size: 21px; margin: 0; font-weight: 400; opacity: 0.85;">Yaratıcı ve modern finansal analizler ve trend öngörüleri</p>
            </div>

            <div style="background: rgba(255, 255, 255, 0.96); border-radius: 24px; padding: 55px; backdrop-filter: blur(20px); border: 2px solid rgba(0, 0, 0, 0.06); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);">
                {{ $newsletterContent }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 50px;">
                <div style="background: linear-gradient(135deg, {{ primary_color }}, {{ secondary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 38px; margin-bottom: 18px;">🎨</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Creative Analysis</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Yaratıcı piyasa analizleri ve alternatif yaklaşımlar</p>
                </div>
                <div style="background: linear-gradient(135deg, {{ secondary_color }}, {{ primary_color }}); color: {{ text_color }}; padding: 35px; border-radius: 20px; text-align: center; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15); border: 2px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 38px; margin-bottom: 18px;">💡</div>
                    <h3 style="margin: 0 0 18px 0; font-size: 22px; font-weight: 700;">Modern Insights</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; line-height: 1.5;">Modern yatırım stratejileri ve trend analizleri</p>
                </div>
            </div>
        </div>';
    }

    private function getCreativeModernFooter()
    {
        return '
        <div style="background: linear-gradient(135deg, {{ primary_color }} 0%, {{ secondary_color }} 100%); padding: 70px 40px; text-align: center; color: {{ text_color }}; font-size: 14px; font-family: \'Poppins\', \'Helvetica Neue\', Arial, sans-serif; position: relative;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, {{ secondary_color }}, {{ primary_color }}, {{ secondary_color }});"></div>
            <div style="margin-top: 10px; margin-bottom: 55px;">
                <h3 style="color: {{ text_color }}; margin: 0 0 28px 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px;">✨ Borsanın Gündemi</h3>
                <p style="margin: 0 0 22px 0; opacity: 0.96; font-size: 19px; line-height: 1.7;">Sayın <strong>#isim#</strong>, yaratıcı ve modern finansal analizlerden bazılarını sizin için derledik. Daha fazlası için <a href="#" style="color: {{ text_color }}; text-decoration: underline; font-weight: 600; opacity: 0.95;">tıklayınız</a></p>
                <p style="margin: 0 0 28px 0; opacity: 0.85; font-size: 17px;">Bu e-posta üyelik ayarlarınız doğrultusunda <strong>#mail#</strong> adresine gönderilmiştir.</p>
            </div>

            <div style="margin: 55px 0; padding: 45px; background: rgba(255,255,255,0.08); border-radius: 24px; border: 2px solid rgba(255,255,255,0.12); backdrop-filter: blur(10px);">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ text_color }}; letter-spacing: 0.5px;">DİJİTAL GÜNDEM MEDYA YAYINCILIK ANONİM ŞİRKETİ</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Ergenekon Mah. Cumhuriyet Cad. Efser Han No: 181 Kat: 8</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📍 Harbiye - Şişli - İstanbul</div>
                <div style="margin-bottom: 18px; opacity: 0.9; font-size: 17px; line-height: 1.6;">📞 Tel: 0 212 294 11 69 / 0 530 849 88 48</div>
                <div style="opacity: 0.9; font-size: 17px; line-height: 1.6;">📠 Faks: 0 212 238 72 07</div>
            </div>

            <div style="margin: 55px 0;">
                <div style="font-weight: 800; margin-bottom: 28px; font-size: 22px; color: {{ text_color }}; letter-spacing: 0.5px;">Bizi Takip Edin</div>
                <div style="display: flex; justify-content: center; gap: 28px;">
                    <a href="#" style="width: 75px; height: 75px; background: #1877f2; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 8px 24px rgba(24, 119, 242, 0.35); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">f</span>
                    </a>
                    <a href="#" style="width: 75px; height: 75px; background: #000000; border-radius: 22px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); border: 2px solid rgba(255,255,255,0.1);">
                        <span style="color: white; font-weight: bold; font-size: 30px;">𝕏</span>
                    </a>
                </div>
            </div>

            <div style="margin-top: 55px; padding-top: 45px; border-top: 2px solid rgba(255,255,255,0.12);">
                <p style="margin: 0 0 22px 0; font-size: 15px; opacity: 0.8;">Artık mail almak istemiyorsanız <a href="#unsubscribe" style="color: {{ text_color }}; text-decoration: underline; font-weight: 500; opacity: 0.95;">bu linke tıklayarak</a> e-posta listemizden çıkabilirsiniz.</p>
                <p style="margin: 0; font-size: 15px; opacity: 0.8;">Bülteni düzgün görüntüleyemiyorsanız tarayıcıda görüntülemek için <a href="#newsletterlink" style="color: {{ text_color }}; text-decoration: underline; font-weight: 500; opacity: 0.95;">tıklayınız</a></p>
            </div>
        </div>';
    }
}
