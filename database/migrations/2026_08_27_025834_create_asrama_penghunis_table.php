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
        Schema::create('asrama_penghunis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->string('nama', 255);
            $table->string('nomor_hp', 50)->nullable();
            $table->enum('status_penghuni', ['Aktif', 'Keluar'])->default('Aktif');
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_penghunis');
    }
};
