<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuliah_courses', function (Blueprint $table) {
            $table->string('ruangan')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('kuliah_courses', function (Blueprint $table) {
            $table->dropColumn('ruangan');
        });
    }
};
