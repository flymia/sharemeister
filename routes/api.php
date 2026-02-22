<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\Api\SystemMetricsController;

Route::get('/health', [SystemMetricsController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/upload', [ScreenshotController::class, 'apiUpload'])->name('api.screenshot.upload');
    Route::post('/upload/raw', [ScreenshotController::class, 'apiUploadRaw'])->name('api.screenshot.upload.raw');
    
});
