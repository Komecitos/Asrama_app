<?php

use Illuminate\Support\Facades\Route;
use Modules\Asrama\Http\Controllers\AsramaController;

Route::prefix('asrama')->name('asrama.')->group(function () {
    Route::redirect('/', '/asrama/data')->name('index');
    Route::get('/data', [AsramaController::class, 'data'])->name('data');
    Route::get('/keuangan', [AsramaController::class, 'keuangan'])->name('keuangan');

    // Kamar Actions
    Route::post('/kamar', [AsramaController::class, 'storeKamar'])->name('kamar.store');
    Route::put('/kamar/{id}', [AsramaController::class, 'updateKamar'])->name('kamar.update');
    Route::delete('/kamar/{id}', [AsramaController::class, 'destroyKamar'])->name('kamar.destroy');

    // Penghuni Actions
    Route::post('/penghuni', [AsramaController::class, 'storePenghuni'])->name('penghuni.store');
    Route::put('/penghuni/{id}', [AsramaController::class, 'updatePenghuni'])->name('penghuni.update');
    Route::delete('/penghuni/{id}', [AsramaController::class, 'destroyPenghuni'])->name('penghuni.destroy');

    // Keuangan Actions
    Route::post('/keuangan', [AsramaController::class, 'storeKeuangan'])->name('keuangan.store');
    Route::delete('/keuangan/{id}', [AsramaController::class, 'destroyKeuangan'])->name('keuangan.destroy');
});
