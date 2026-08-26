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
        Schema::create('asrama_iurans', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->foreignId('penghuni_id')->nullable()->constrained('asrama_penghunis')->onDelete('cascade');
            $table->string('fasilitas_key', 50)->nullable();
            $table->integer('nominal')->default(0);
            $table->boolean('status_lunas')->default(false);
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'penghuni_id', 'fasilitas_key'], 'idx_asrama_iuran_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_iurans');
    }
};
