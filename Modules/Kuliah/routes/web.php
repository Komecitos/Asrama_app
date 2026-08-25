<?php

use Illuminate\Support\Facades\Route;
use Modules\Kuliah\Http\Controllers\KuliahController;

Route::prefix('kuliah')->name('kuliah.')->group(function () {
    Route::redirect('/', '/kuliah/matakuliah')->name('index');
    Route::get('/matakuliah', [KuliahController::class, 'matakuliah'])->name('matakuliah');
    Route::post('/courses', [KuliahController::class, 'store'])->name('course.store');
    Route::put('/courses/{kode}', [KuliahController::class, 'update'])->name('course.update');
    Route::delete('/courses/{kode}', [KuliahController::class, 'destroy'])->name('course.destroy');
});
