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
        Schema::table('asrama_penghunis', function (Blueprint $table) {
            $table->string('kampus', 255)->nullable()->after('nomor_hp');
            $table->string('asal_kampung', 255)->nullable()->after('kampus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asrama_penghunis', function (Blueprint $table) {
            $table->dropColumn(['kampus', 'asal_kampung']);
        });
    }
};
