<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScreenshotController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/upload', [ScreenshotController::class, 'apiUpload'])->name('api.screenshot.upload');
Route::middleware('auth:sanctum')->post('/upload/raw', [ScreenshotController::class, 'apiUploadRaw']);
