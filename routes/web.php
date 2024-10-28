<?php

use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
});

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', function () {
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::get('/screenshots/upload', [ScreenshotController::class, "create"])->name('screenshot.upload');
    Route::post('/screenshots/upload', [ScreenshotController::class, "store"])->name('screenshot.upload');


    Route::get('/screenshots/list', [ScreenshotController::class, "index"])->name('screenshot.list');

    Route::get('/screenshots/{screenshot}', [ScreenshotController::class, "show"])->name('screenshot.show');

    Route::get('/account/settings', [UserController::class, "index"])->name('account.settings');
});
