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
        // Bu migration artık gerekli değil çünkü create_posts_table migration'ında
        // status enum'u zaten 'scheduled' değerini içeriyor
        // Eğer veritabanında eski enum varsa güncelle
        if (! Schema::hasTable('posts')) {
            return;
        }

        $driverName = DB::getDriverName();

        if ($driverName === 'pgsql') {
            // Önce mevcut constraint'i kaldır (varsa)
            DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS check_status');

            // PostgreSQL'de enum tipini kontrol et ve güncelle
            $enumTypes = DB::select("
                SELECT t.typname 
                FROM pg_type t 
                JOIN pg_enum e ON t.oid = e.enumtypid 
                WHERE t.typname LIKE '%status%'
                GROUP BY t.typname
            ");

            // Enum tipi varsa ve 'scheduled' değeri yoksa ekle
            foreach ($enumTypes as $enumType) {
                $typeName = $enumType->typname;
                $hasScheduled = DB::selectOne("
                    SELECT 1 
                    FROM pg_enum 
                    WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = ?) 
                    AND enumlabel = 'scheduled'
                ", [$typeName]);

                if (! $hasScheduled) {
                    try {
                        // PostgreSQL'de enum'a değer eklemek için transaction dışında olmalı
                        DB::statement("ALTER TYPE {$typeName} ADD VALUE IF NOT EXISTS 'scheduled'");
                    } catch (\Exception $e) {
                        // Enum tipi yoksa veya zaten varsa devam et
                    }
                }
            }

            // Check constraint ekle (enum veya varchar için)
            DB::statement("ALTER TABLE posts ADD CONSTRAINT check_status CHECK (status IN ('draft', 'published', 'scheduled', 'archived'))");
        } else {
            // Diğer veritabanları için sadece constraint ekle
            DB::statement("ALTER TABLE posts ADD CONSTRAINT check_status CHECK (status IN ('draft', 'published', 'scheduled', 'archived'))");
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

        // Constraint'i kaldır
        DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS check_status');
    }
};
