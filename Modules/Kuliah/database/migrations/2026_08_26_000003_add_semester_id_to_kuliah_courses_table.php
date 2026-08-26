<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuliah_courses', function (Blueprint $table) {
            $table->foreignId('semester_id')
                ->nullable()
                ->after('id')
                ->constrained('kuliah_semesters')
                ->nullOnDelete();
        });

        DB::table('kuliah_courses')->orderBy('id')->each(function ($course) {
            $entries = is_string($course->nilai) ? json_decode($course->nilai, true) : $course->nilai;
            $semesterNumber = collect(is_array($entries) ? $entries : [])
                ->map(fn($entry) => is_array($entry) ? (int) ($entry['semester'] ?? 0) : 0)
                ->first(fn($number) => $number > 0);

            if ($semesterNumber) {
                $semesterId = DB::table('kuliah_semesters')
                    ->where('number', $semesterNumber)
                    ->value('id');

                DB::table('kuliah_courses')
                    ->where('id', $course->id)
                    ->update(['semester_id' => $semesterId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('kuliah_courses', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};
