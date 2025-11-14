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
        Schema::create('posts', function (Blueprint $table) {
            $table->id('post_id');
            $table->unsignedBigInteger('author_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->json('spot_data')->nullable();
            $table->timestamp('published_date')->nullable();
            $table->longText('content')->nullable();
            $table->enum('post_type', ['news', 'gallery', 'video'])->default('news');
            $table->enum('post_position', ['normal', 'manşet', 'sürmanşet', 'öne çıkanlar'])->default('normal');
            $table->integer('post_order')->default(0);
            $table->boolean('is_comment')->default(true);
            $table->boolean('is_mainpage')->default(false);
            $table->string('redirect_url')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->boolean('is_photo')->default(false);
            $table->string('agency_name')->nullable();
            $table->unsignedBigInteger('agency_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('embed_code')->nullable();
            $table->boolean('in_newsletter')->default(false);
            $table->boolean('no_ads')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Performance indexes
            $table->index(['status', 'published_date']);
            $table->index(['post_type', 'status']);
            $table->index(['post_position', 'is_mainpage']);
            $table->index(['author_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('published_date');
            $table->index('view_count');
            $table->index('post_order');
            $table->index('slug');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
