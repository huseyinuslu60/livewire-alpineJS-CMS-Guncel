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
        Schema::create('categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->enum('type', ['news', 'gallery', 'video'])->default('news');
            $table->boolean('show_in_menu')->default(true);
            $table->integer('weight')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            // Foreign keys
            $table->foreign('parent_id')->references('category_id')->on('categories')->onDelete('cascade');

            // Indexes
            $table->index(['status', 'type']);
            $table->index(['parent_id', 'weight']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
