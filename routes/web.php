<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SPA entrypoint and Sanctum CSRF cookie route.
|
*/

// Sanctum CSRF cookie (for SPA auth)
Route::get('/sanctum/csrf-cookie', \Laravel\Sanctum\Http\Controllers\CsrfCookieController::class.'@show');

// SPA catch‑all: let Vue handle routing
Route::view('/{any?}', 'app')
    ->where('any', '.*')
    ->name('spa');
