<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});


use Illuminate\Support\Facades\Artisan;

Route::get('/run-seed', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return "Bhai, Database Seed ho gaya!";
});

Route::get('/run-storage', function () {
    Artisan::call('storage:link', ['--force' => true]);
    return "Bhai, Storage link ho gaya!";
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');