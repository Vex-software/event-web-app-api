<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DBController;
use App\Http\Controllers\UserController;



Route::get('/', function () {
    return "Welcome to the home page!";
    // return response('', 204); // 204 No Content
})->name('welcome');




Route::get('clubs', [DBController::class, 'clubs'])->name('clubs.index');
Route::get('users', [DBController::class, 'users'])->name('users.index');


Route::get('clubs/{clubId}/katil/{userId}', [DBController::class, 'joinClub'])->name('clubs.katil');



