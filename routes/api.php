<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\Api\SystemMetricsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/upload', [ScreenshotController::class, 'apiUpload'])->name('api.screenshot.upload');
Route::middleware('auth:sanctum')->post('/upload/raw', [ScreenshotController::class, 'apiUploadRaw']);


Route::get('/health', [SystemMetricsController::class, 'index']);