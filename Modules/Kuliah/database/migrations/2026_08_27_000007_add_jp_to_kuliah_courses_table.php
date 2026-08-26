<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kuliah_courses', 'jp')) {
            Schema::table('kuliah_courses', function (Blueprint $table) {
                $table->unsignedTinyInteger('jp')->nullable()->after('sks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kuliah_courses', 'jp')) {
            Schema::table('kuliah_courses', function (Blueprint $table) {
                $table->dropColumn('jp');
            });
        }
    }
};
