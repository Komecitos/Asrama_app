<?php

use Illuminate\Support\Facades\Route;
use Modules\Kuliah\Http\Controllers\KuliahController;

Route::prefix('kuliah')->name('kuliah.')->group(function () {
    Route::redirect('/', '/kuliah/jadwal')->name('index');
    Route::get('/matakuliah', [KuliahController::class, 'matakuliah'])->name('matakuliah');
    Route::get('/jadwal', [KuliahController::class, 'jadwal'])->name('jadwal');
    Route::get('/semester/{semester}', [KuliahController::class, 'semester'])->name('semester.show');
    Route::post('/courses', [KuliahController::class, 'store'])->name('course.store');
    Route::put('/semester-courses/{semesterCourse}/schedule', [KuliahController::class, 'updateSchedule'])->name('semester-course.schedule.update');
    Route::delete('/semester-courses/{semesterCourse}', [KuliahController::class, 'destroySemesterCourse'])->name('semester-course.destroy');
    Route::put('/courses/{kode}', [KuliahController::class, 'update'])->name('course.update');
    Route::delete('/courses/{kode}', [KuliahController::class, 'destroy'])->name('course.destroy');
});
