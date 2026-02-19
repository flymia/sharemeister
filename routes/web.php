<?php

use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/setup-required', function () {
    return view('errors.setup');
})->name('setup.required');

Route::get('/', function () {
    return view('landing.index');
})->name('landing');

Route::get('/health', HealthController::class);

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/home', [ScreenshotController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/screenshots/upload', [ScreenshotController::class, "create"])->name('screenshot.upload');
    Route::post('/screenshots/upload', [ScreenshotController::class, "store"])->name('screenshot.upload');

    Route::get('/screenshots/list', [ScreenshotController::class, 'index'])->name('screenshot.list');

    Route::get('/screenshots/details/{id}', [ScreenshotController::class, "show"])->name('screenshot.details');
    Route::delete('/screenshots/delete/{id}', [ScreenshotController::class, "destroy"])->name('screenshot.delete');
    Route::post('/screenshots/details/{id}', [ScreenshotController::class, 'updateMetadata'])->name('screenshot.update-metadata');

    Route::get('/account/settings', [UserController::class, "index"])->name('account.settings');
    Route::post('/account/settings/update', [UserController::class, 'update'])->name('account.settings.update');    
    Route::post('/account/settings/generateapikey', [UserController::class, "generateapikey"])->name('account.settings.generateapikey');
    Route::post('/account/settings/deleteapikey', [UserController::class, "deleteapikey"])->name('account.settings.deleteapikey');
    Route::post('/account/settings/password', [UserController::class, 'updatePassword'])->name('account.settings.password');
    Route::get('/account/settings/download-sxcu', [UserController::class, 'downloadSxcu'])->name('account.settings.sxcu');
    Route::get('/account/settings/download-bash', [UserController::class, 'downloadBashScript'])->name('account.settings.bash');
});

Route::get('/screenshots/{filename}', [ScreenshotController::class, 'rawShow'])->name('screenshot.raw');