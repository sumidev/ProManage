<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});


use Illuminate\Support\Facades\Artisan;

Route::get('/run-seed', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return "Bhai, Database Seed ho gaya!";
});