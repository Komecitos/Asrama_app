<?php

use Illuminate\Support\Facades\Route;
use Modules\Asrama\Http\Controllers\AsramaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('asramas', AsramaController::class)->names('asrama');
});
