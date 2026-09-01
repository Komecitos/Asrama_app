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
        Schema::create('asrama_kamars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kamar', 50);
            $table->integer('lantai')->default(1);
            $table->integer('kapasitas')->default(2);
            $table->unsignedBigInteger('harga_per_bulan')->default(500000);
            $table->string('status', 50)->default('Tersedia');
            $table->text('fasilitas')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_kamars');
    }
};
