<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\Api\SystemMetricsController;

// Public routes
Route::get('/health', [SystemMetricsController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [ScreenshotController::class, 'apiUpload'])->name('api.screenshot.upload');
    Route::post('/upload/raw', [ScreenshotController::class, 'apiUploadRaw'])->name('api.screenshot.upload.raw');    
});
