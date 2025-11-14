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
        Schema::create('files', function (Blueprint $table) {
            $table->id('file_id');
            $table->unsignedBigInteger('post_id');
            $table->string('title')->nullable();
            $table->string('type')->default('news'); // news, gallery, video
            $table->string('file_path');
            $table->boolean('primary')->default(false);
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');

            // Indexes
            $table->index(['post_id', 'primary']);
            $table->index(['post_id', 'type']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
