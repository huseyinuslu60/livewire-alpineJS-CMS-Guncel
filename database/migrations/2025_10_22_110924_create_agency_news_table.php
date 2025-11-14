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
        Schema::create('agency_news', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('tags')->nullable();
            $table->string('original_id')->nullable();
            $table->string('agency_id')->nullable();
            $table->string('category')->nullable();
            $table->boolean('has_image')->default(false);
            $table->string('file_path')->nullable();
            $table->text('sites')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'category']);
            $table->index('has_image');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_news');
    }
};
