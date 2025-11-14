<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL için trigram extension ve index'leri
        if (DB::getDriverName() === 'pgsql') {
            // pg_trgm extension'ı oluştur
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

            // Posts tablosu için trigram index'leri
            DB::statement('CREATE INDEX IF NOT EXISTS idx_posts_title_trgm ON posts USING gin (title gin_trgm_ops)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_posts_slug_trgm ON posts USING gin (slug gin_trgm_ops)');

            // Partial index: published_date DESC WHERE status = 'published'
            DB::statement("CREATE INDEX IF NOT EXISTS idx_posts_pubdate_published ON posts (published_date DESC) WHERE status = 'published'");
        }

        // Pivot tablolar için unique ve composite index'ler
        // posts_categories tablosu
        if (Schema::hasTable('posts_categories')) {
            Schema::table('posts_categories', function (Blueprint $table) {
                // Unique constraint zaten var, composite index ekle
                $table->unique(['post_id', 'category_id'], 'post_categories_post_category_unique');
                $table->index(['category_id', 'post_id'], 'post_categories_category_post_idx');
            });
        }

        // posts_tags tablosu
        if (Schema::hasTable('posts_tags')) {
            Schema::table('posts_tags', function (Blueprint $table) {
                // Unique constraint zaten var, composite index ekle
                $table->unique(['post_id', 'tag_id'], 'posts_tags_post_tag_unique');
                $table->index(['tag_id', 'post_id'], 'posts_tags_tag_post_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL trigram index'lerini kaldır
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_posts_pubdate_published');
            DB::statement('DROP INDEX IF EXISTS idx_posts_slug_trgm');
            DB::statement('DROP INDEX IF EXISTS idx_posts_title_trgm');
            // Extension'ı kaldırmayız çünkü başka yerlerde kullanılıyor olabilir
        }

        // Pivot tablolardan index'leri kaldır
        if (Schema::hasTable('posts_categories')) {
            Schema::table('posts_categories', function (Blueprint $table) {
                $table->dropIndex('post_categories_category_post_idx');
                $table->dropUnique('post_categories_post_category_unique');
            });
        }

        if (Schema::hasTable('posts_tags')) {
            Schema::table('posts_tags', function (Blueprint $table) {
                $table->dropIndex('posts_tags_tag_post_idx');
                $table->dropUnique('posts_tags_post_tag_unique');
            });
        }
    }
};
