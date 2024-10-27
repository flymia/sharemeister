<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
});

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', function () {
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::get('/screenshots/upload', function () {
        return view('screenshot.upload');
    })->name('screenshot.upload');

    Route::get('/screenshots/{screenshot}', function ($screenshot) {
        return view('screenshot.show', ['screenshot' => $screenshot]);
    })->name('screenshot.show');

});
