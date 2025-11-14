<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Modül adı (articles, categories, roles, user)
            $table->string('display_name'); // Görünen ad (Makaleler, Kategoriler, Roller, Kullanıcılar)
            $table->text('description')->nullable(); // Modül açıklaması
            $table->string('version')->default('1.0.0'); // Modül versiyonu
            $table->boolean('is_active')->default(true); // Aktif/Pasif durumu
            $table->string('icon')->default('feather icon-package'); // Modül ikonu
            $table->string('route_prefix')->nullable(); // Route prefix
            $table->json('permissions')->nullable(); // Modül izinleri
            $table->integer('sort_order')->default(0); // Sıralama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
