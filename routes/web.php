<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DBController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleController;
use Laravel\Socialite\Two\GoogleProvider;

Route::get('/', function () {
    return "Welcome to the home page!";
    // return response('', 204); // 204 No Content
})->name('welcome');


Route::get('/login', function () {
    return view('login.google');
})->name('login');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);



Route::get('clubs', [DBController::class, 'clubs'])->name('clubs.index');
Route::get('users', [DBController::class, 'users'])->name('users.index');


Route::get('clubs/{clubId}/katil/{userId}', [DBController::class, 'joinClub'])->name('clubs.katil');
