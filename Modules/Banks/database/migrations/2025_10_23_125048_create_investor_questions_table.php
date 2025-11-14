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
        Schema::create('investor_questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->string('title');
            $table->string('name');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->enum('status', ['pending', 'answered', 'rejected'])->default('pending');
            $table->string('stock')->nullable();
            $table->string('email');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('hit')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('answer_title')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_questions');
    }
};
