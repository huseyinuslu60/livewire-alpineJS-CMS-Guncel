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
        Schema::create('lastminutes', function (Blueprint $table) {
            $table->id('lastminute_id');
            $table->string('title');
            $table->string('redirect')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->integer('weight')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'end_at']);
            $table->index(['weight', 'created_at']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lastminutes');
    }
};
