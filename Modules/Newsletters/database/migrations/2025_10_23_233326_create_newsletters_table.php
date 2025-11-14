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
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id('newsletter_id');
            $table->string('name');
            $table->string('status')->default('draft'); // draft, sending, sent, failed
            $table->string('mail_status')->default('pending'); // pending, sent, failed
            $table->string('mail_subject');
            $table->text('mail_body');
            $table->text('mail_body_raw')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->boolean('reklam')->default(false);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
