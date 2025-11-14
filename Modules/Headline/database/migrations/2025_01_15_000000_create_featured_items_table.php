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
        Schema::create('featured_items', function (Blueprint $table) {
            $table->id();
            $table->string('zone'); // manset, surmanset, one_cikanlar
            $table->string('subject_type'); // post, article
            $table->unsignedBigInteger('subject_id');
            $table->integer('slot')->nullable(); // 1, 2, 3... (null = pinned but not slotted)
            $table->integer('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['zone', 'is_active']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['zone', 'slot']);
            $table->index(['starts_at', 'ends_at']);

            // Unique constraint: one item per zone slot
            $table->unique(['zone', 'slot'], 'unique_zone_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_items');
    }
};
