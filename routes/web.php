<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
});

Route::middleware(['auth', 'verified'])->get('/home', function () {
    return view('dashboard.dashboard');
})->name('dashboard');
