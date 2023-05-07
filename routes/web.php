<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DBController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('clubs', [DBController::class, 'clubs'])->name('clubs.index');
Route::get('users', [DBController::class, 'users'])->name('users.index');


Route::get('clubs/{clubId}/katil/{userId}', [DBController::class, 'joinClub'])->name('clubs.katil');



