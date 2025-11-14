<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AgencyNews\Models\AgencyNews;

class AgencyNewsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test verilerini oluştur
        AgencyNews::create([
            'title' => 'Test Haber 1 - Teknoloji',
            'summary' => 'Bu bir test haberidir. Teknoloji kategorisinde yer almaktadır.',
            'content' => 'Bu haber içeriği test amaçlı oluşturulmuştur. Teknoloji dünyasından önemli gelişmeler...',
            'tags' => 'teknoloji,test,haber',
            'original_id' => 'test-001',
            'agency_id' => 1,
            'category' => 'Teknoloji',
            'has_image' => true,
            'file_path' => '/images/test1.jpg',
            'sites' => 'site1.com,site2.com',
        ]);

        AgencyNews::create([
            'title' => 'Test Haber 2 - Spor',
            'summary' => 'Spor dünyasından son gelişmeler.',
            'content' => 'Spor haberleri ve son dakika gelişmeleri burada yer almaktadır...',
            'tags' => 'spor,haber,son dakika',
            'original_id' => 'test-002',
            'agency_id' => 2,
            'category' => 'Spor',
            'has_image' => false,
            'file_path' => null,
            'sites' => 'spor.com',
        ]);

        AgencyNews::create([
            'title' => 'Test Haber 3 - Ekonomi',
            'summary' => 'Ekonomik gelişmeler ve piyasa analizi.',
            'content' => 'Ekonomi dünyasından önemli gelişmeler ve analizler...',
            'tags' => 'ekonomi,piyasa,analiz',
            'original_id' => 'test-003',
            'agency_id' => 3,
            'category' => 'Ekonomi',
            'has_image' => true,
            'file_path' => '/images/economy.jpg',
            'sites' => 'ekonomi.com,finans.com',
        ]);
    }
}
