<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuliah_semester_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('kuliah_semesters')->cascadeOnDelete();
            $table->string('kode');
            $table->string('nama');
            $table->unsignedTinyInteger('sks');
            $table->string('jenis')->default('Core');
            $table->string('dosen')->nullable();
            $table->string('ruangan')->nullable();
            $table->string('hari')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->timestamps();

            $table->index(['semester_id', 'kode']);
        });

        DB::table('kuliah_courses')
            ->whereNotNull('semester_id')
            ->orderBy('id')
            ->each(function ($course) {
                DB::table('kuliah_semester_courses')->insert([
                    'semester_id' => $course->semester_id,
                    'kode' => $course->kode,
                    'nama' => $course->nama,
                    'sks' => $course->sks,
                    'jenis' => $course->jenis,
                    'dosen' => $course->dosen,
                    'ruangan' => $course->ruangan ?? null,
                    'hari' => $course->hari ?? null,
                    'jam_mulai' => $course->jam_mulai ?? null,
                    'jam_selesai' => $course->jam_selesai ?? null,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuliah_semester_courses');
    }
};
