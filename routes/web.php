<?php

use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
});

Route::get('/share/{filename}', [ScreenshotController::class, 'rawshow'])->name('screenshot.show');

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', function () {
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::get('/screenshots/upload', [ScreenshotController::class, "create"])->name('screenshot.upload');
    Route::post('/screenshots/upload', [ScreenshotController::class, "store"])->name('screenshot.upload');

    Route::get('/screenshots/details/{id}', [ScreenshotController::class, "show"])->name('screenshot.details');

    Route::delete('/screenshots/delete/{id}', [ScreenshotController::class, "destroy"])->name('screenshot.delete');

    Route::get('/screenshots/list', [ScreenshotController::class, "index"])->name('screenshot.list');

    Route::get('/account/settings', [UserController::class, "index"])->name('account.settings');
    Route::post('/account/settings/update', [UserController::class, "update"])->name('account.settings.update');
    Route::post('/account/settings/generateapikey', [UserController::class, "generateapikey"])->name('account.settings.generateapikey');
});
