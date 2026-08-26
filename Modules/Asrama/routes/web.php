<?php

use Illuminate\Support\Facades\Route;
use Modules\Asrama\Http\Controllers\AsramaController;

Route::prefix('asrama')->name('asrama.')->group(function () {
    Route::get('/', [AsramaController::class, 'index'])->name('index');
});
