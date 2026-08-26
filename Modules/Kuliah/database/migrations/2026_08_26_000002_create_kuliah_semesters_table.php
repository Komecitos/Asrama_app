<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuliah_semesters', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        foreach (range(1, 14) as $number) {
            DB::table('kuliah_semesters')->insert([
                'number' => $number,
                'name' => 'Semester ' . $number,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kuliah_semesters');
    }
};