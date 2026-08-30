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
        Schema::create('asrama_wifi_configs', function (Blueprint $table) {
            $table->id();
            $table->string('ssid')->default('Asrama-Mahulu-HighSpeed');
            $table->string('password')->default('MahuluAktif#2026');
            $table->integer('bulan')->default(date('n'));
            $table->integer('tahun')->default(date('Y'));
            $table->text('catatan')->nullable();
            $table->text('template_lunas')->nullable();
            $table->text('template_tagihan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_wifi_configs');
    }
};
