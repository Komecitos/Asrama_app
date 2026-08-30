<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AsramaController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::patch('/penghuni/{id}/keluar', [AsramaController::class, 'keluarPenghuni'])->name('penghuni.keluar');
    Route::patch('/penghuni/{id}/reactivate', [AsramaController::class, 'reactivatePenghuni'])->name('penghuni.reactivate');
    Route::put('/penghuni/{id}', [AsramaController::class, 'updatePenghuni'])->name('penghuni.update');
    Route::delete('/penghuni/{id}', [AsramaController::class, 'destroyPenghuni'])->name('penghuni.destroy');

    // Keuangan & Matriks Export Actions
    Route::get('/keuangan/export/excel', [AsramaController::class, 'exportKeuanganExcel'])->name('keuangan.export.excel');
    Route::get('/keuangan/export/pdf', [AsramaController::class, 'exportKeuanganPdf'])->name('keuangan.export.pdf');
    Route::get('/keuangan/matriks/export/excel', [AsramaController::class, 'exportMatriksExcel'])->name('keuangan.matriks.export.excel');
    Route::get('/keuangan/matriks/export/pdf', [AsramaController::class, 'exportMatriksPdf'])->name('keuangan.matriks.export.pdf');

    // Keuangan & Matriks Main Actions
    Route::get('/keuangan/matriks', [AsramaController::class, 'matriksKeuangan'])->name('keuangan.matriks');
    Route::post('/keuangan/matriks/update', [AsramaController::class, 'updateMatriksIuran'])->name('keuangan.matriks.update');
    Route::post('/keuangan', [AsramaController::class, 'storeKeuangan'])->name('keuangan.store');
    Route::put('/keuangan/{id}', [AsramaController::class, 'updateKeuangan'])->name('keuangan.update');
    Route::delete('/keuangan/{id}', [AsramaController::class, 'destroyKeuangan'])->name('keuangan.destroy');

    // WiFi Distribution & Configuration
    Route::post('/keuangan/wifi-config', [AsramaController::class, 'saveWifiConfig'])->name('wifi.config.save');
    Route::get('/keuangan/wifi-distribusi', [AsramaController::class, 'getWifiDistributionData'])->name('wifi.distribusi');
});
