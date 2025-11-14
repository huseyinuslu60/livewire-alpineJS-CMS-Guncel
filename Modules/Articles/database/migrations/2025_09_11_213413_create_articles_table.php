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
        Schema::create('articles', function (Blueprint $table) {
            $table->id('article_id');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->longText('article_text');
            $table->integer('hit')->default(0);
            $table->boolean('show_on_mainpage')->default(false);
            $table->boolean('is_commentable')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->enum('status', ['draft', 'published', 'pending'])->default('draft');
            $table->unsignedBigInteger('site_id')->nullable();

            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['author_id']);
            $table->index(['site_id']);
            $table->index(['show_on_mainpage']);
            $table->index(['is_commentable']);
            $table->index(['created_at']);
            $table->index(['deleted_at']);

            // Foreign keys (commented out for now)
            // $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
