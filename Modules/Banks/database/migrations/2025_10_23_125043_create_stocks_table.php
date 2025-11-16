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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id('stock_id');
            $table->string('name');
            $table->string('unvan');
            $table->date('kurulus_tarihi')->nullable();
            $table->date('ilk_islem_tarihi')->nullable();
            $table->text('merkez_adres')->nullable();
            $table->string('web')->nullable();
            $table->string('telefon')->nullable();
            $table->string('faks')->nullable();
            $table->integer('personel_sayisi')->nullable();
            $table->string('genel_mudur')->nullable();
            $table->text('yonetim_kurulu')->nullable();
            $table->text('faaliyet_alani')->nullable();
            $table->text('endeksler')->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('last_status')->default('active');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
