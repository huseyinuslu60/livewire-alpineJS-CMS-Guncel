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
        Schema::create('newsletter_posts', function (Blueprint $table) {
            $table->id('record_id');
            $table->unsignedBigInteger('newsletter_id');
            $table->unsignedBigInteger('post_id');
            $table->integer('order')->default(0);
            $table->integer('hit')->default(0);
            $table->timestamps();

            $table->foreign('newsletter_id')->references('newsletter_id')->on('newsletters');
            $table->foreign('post_id')->references('post_id')->on('posts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_posts');
    }
};
