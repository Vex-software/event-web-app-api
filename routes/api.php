<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;


Route::get('/', function () {
    return response()->json(['message' => 'Buyrun burası API!']);
});


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/lost-password', [AuthController::class, 'lostPassword'])->middleware('auth:api');
    // Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    // Route::post('/refresh', [AuthController::class, 'refresh']);
    // Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    // Route::get('/email/resend', [AuthController::class, 'resendEmail'])->name('verification.resend');
    // Route::get('/password/reset/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    // Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});


Route::middleware('auth:api')->group(function () {
    Route::get('/who-am-i', [UserController::class, 'whoAmI']); // oturum bilgileri kime ait?
    Route::get('/my-clubs', [UserController::class, 'myClubs']); // oturum açan kullanıcının üye olduğu kulüpler
    Route::get('/my-events', [UserController::class, 'myEvents']); // oturum açan kullanıcının dahil olduğu etkinlikler

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Kullanıcılar
        Route::get('/{id}', [UserController::class, 'show']); // Kullanıcı bilgileri
        Route::get('/{id}/clubs', [UserController::class, 'userClubs']); // Kullanıcının üye olduğu kulüpler
        Route::get('/{id}/events', [UserController::class, 'userEvents']); // Kullanıcının dahil olduğu etkinlikler

        Route::post('/join-club/{clubId}', [UserController::class, 'joinClub']); // Kulübe katıl
        Route::post('/leave-club/{clubId}', [UserController::class, 'leaveClub']); // Kulüpten ayrıl

        Route::post('/join-event/{eventId}', [UserController::class, 'joinEvent']); // Etkinliğe katıl
        Route::post('/leave-event/{eventId}', [UserController::class, 'leaveEvent']); // Etkinlikten ayrıl
    });


    Route::prefix('clubs')->group(function () {
        Route::get('/', [ClubController::class, 'index']); // Kulüpler
        Route::get('/{id}', [ClubController::class, 'show']); // Kulüp bilgileri
        Route::get('/{id}/users', [ClubController::class, 'clubUsers']); // Kulübe üye olan kullanıcılar
        Route::get('/{id}/events', [ClubController::class, 'clubEvents']); // Kulübün etkinlikleri
    });


    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']); // Etkinlikler
        Route::get('/{id}', [EventController::class, 'show']); // Etkinlik bilgileri
        Route::get('/{id}/users', [EventController::class, 'eventUsers']); // Etkinliğe katılan kullanıcılar
        Route::get('/{id}/club', [EventController::class, 'eventClub']); // Etkinliğin ait olduğu kulüp
    });

});
