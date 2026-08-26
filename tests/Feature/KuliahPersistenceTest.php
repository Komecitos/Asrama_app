<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kuliah\Models\KuliahCourse;
use Tests\TestCase;

class KuliahPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kuliah_courses_are_persisted_to_database(): void
    {
        $course = KuliahCourse::create([
            'kode' => 'MK-777',
            'nama' => 'Database Persistence Test',
            'sks' => 3,
            'nilai' => [
                ['semester' => 1, 'grade' => 'A'],
            ],
            'status' => 'Passed',
            'jenis' => 'Core',
            'dosen' => 'Test Lecturer',
        ]);

        $this->assertDatabaseHas('kuliah_courses', [
            'id' => $course->id,
            'kode' => 'MK-777',
            'nama' => 'Database Persistence Test',
        ]);
    }
}
