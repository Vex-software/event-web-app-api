<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DBController;
use App\Http\Controllers\Guest\GithubController;
use App\Http\Controllers\Guest\GoogleController;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

Route::get('/', function () {
    return "Hi";
    return response('', 204); // 204 No Content
})->name('welcome');

Route::get('/login', function () {
    return view('login.index');
})->name('login');

Route::post('login', function (Request $request) {
    $user = json_decode($request->data, true);
    echo "Sayın " . $user['name'] . " " . $user['surname'] . " başarıyla giriş yaptınız.";
    echo "<br><pre>";
    print_r(json_decode($request->data, true));
    echo "</pre><br>";

})->name('login.post');


Route::get('/react', function (Illuminate\Http\Request $request) {
    $cookies = $request->cookies->all();
    return response()->json($cookies);
})->name('deneme');


Route::get('auth/google', [DBController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [DBController::class, 'handleGoogleCallback']);


Route::get('clubs', [DBController::class, 'clubs'])->name('clubs.index');
Route::get('users', [DBController::class, 'users'])->name('users.index');


Route::get('clubs/{clubId}/katil/{userId}', [DBController::class, 'joinClub'])->name('clubs.katil');
