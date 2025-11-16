<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Posts tablosu var mı kontrol et
        if (! Schema::hasTable('posts')) {
            return;
        }

        // Galeri postlarının spot_data'sını content'e taşı
        $galleryPosts = DB::table('posts')
            ->where('post_type', 'gallery')
            ->whereNotNull('spot_data')
            ->get();

        foreach ($galleryPosts as $post) {
            $spotData = json_decode($post->spot_data, true);

            if (is_array($spotData) && ! empty($spotData)) {
                // spot_data'yı content'e taşı
                DB::table('posts')
                    ->where('post_id', $post->post_id)
                    ->update([
                        'content' => json_encode($spotData, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Posts tablosu var mı kontrol et
        if (! Schema::hasTable('posts')) {
            return;
        }

        // Geri alma işlemi - content'i spot_data'ya taşı
        $galleryPosts = DB::table('posts')
            ->where('post_type', 'gallery')
            ->whereNotNull('content')
            ->get();

        foreach ($galleryPosts as $post) {
            $content = $post->content;
            $decoded = json_decode($content, true);

            if (is_array($decoded) && ! empty($decoded)) {
                // content'i spot_data'ya taşı
                DB::table('posts')
                    ->where('post_id', $post->post_id)
                    ->update([
                        'spot_data' => json_encode($decoded, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
