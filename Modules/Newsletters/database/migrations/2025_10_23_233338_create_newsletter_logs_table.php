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
        Schema::create('newsletter_logs', function (Blueprint $table) {
            $table->id('record_id');
            $table->unsignedBigInteger('newsletter_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('link')->nullable();
            $table->string('type'); // click, open, bounce, unsubscribe
            $table->string('status')->default('success'); // success, failed
            $table->timestamps();

            $table->foreign('newsletter_id')->references('newsletter_id')->on('newsletters');
            $table->foreign('user_id')->references('user_id')->on('newsletter_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_logs');
    }
};
