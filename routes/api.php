<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;

use App\Http\Controllers\MemberController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClubManagerController;
use App\Http\Controllers\GoogleController;





Route::get('/', function () {
    return response()->json(['message' => 'Buyrun burası API!']);
});


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/lost-password', [AuthController::class, 'lostPassword'])->middleware('auth:api');

    //denenmedi
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->name('verification.verify');

    Route::get('/email/resend', [AuthController::class, 'resendEmail'])->name('verification.resend');
});


Route::middleware('auth:api')->group(function () {
    Route::get('/who-am-i', [UserController::class, 'whoAmI']); // oturum bilgileri kime ait?
    Route::get('/my-clubs', [UserController::class, 'myClubs']); // oturum açan kullanıcının üye olduğu kulüpler
    Route::get('/my-events', [UserController::class, 'myEvents']); // oturum açan kullanıcının dahil olduğu etkinlikler
    Route::get('/my-photo', [UserController::class, 'myPhoto']); // oturum açan kullanıcının profil fotoğrafı


    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Kullanıcılar
        Route::get('/{id}', [UserController::class, 'show']); // Kullanıcı bilgileri
        Route::get('/{id}/clubs', [UserController::class, 'userClubs']); // Kullanıcının üye olduğu kulüpler
        Route::get('/{id}/events', [UserController::class, 'userEvents']); // Kullanıcının dahil olduğu etkinlikler
        Route::get('/{id}/photo', [UserController::class, 'userPhoto']); // Kullanıcının profil fotoğrafı

        Route::post('/join-club/{clubId}', [UserController::class, 'joinClub']); // Kulübe katıl
        Route::post('/leave-club/{clubId}', [UserController::class, 'leaveClub']); // Kulüpten ayrıl

        Route::post('/join-event/{eventId}', [UserController::class, 'joinEvent']); // Etkinliğe katıl
        Route::post('/leave-event/{eventId}', [UserController::class, 'leaveEvent']); // Etkinlikten ayrıl

        Route::post('/update-profile', [UserController::class, 'updateProfile']); // oturum açan kullanıcının profil bilgilerini güncelle
        Route::post('/update-password', [UserController::class, 'updatePassword']); // oturum açan kullanıcının şifresini güncelle

        Route::post('delete-photo', [UserController::class, 'deletePhoto']); // oturum açan kullanıcının profil fotoğrafını sil
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

    Route::prefix('admin')->middleware('check.admin')->group(function () {
        Route::post('/update-user-role/{id}', [AdminController::class, 'updateRole']); // Kullanıcının yetkisini değiştir
        Route::post('/delete-user/{id}', [AdminController::class, 'deleteUser']); // Kullanıcıyı sil
        Route::post('/restore-user/{id}', [AdminController::class, 'restoreUser']); // Kullanıcıyı geri yükle
        Route::get('/deleted-users', [AdminController::class, 'deletedUsers']); // Silinmiş kullanıcılar




        //yapilacaklar

        Route::post('/create-event', [AdminController::class, 'createEvent']); // Etkinlik oluştur
        Route::post('/update-event/{id}', [AdminController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::post('/delete-event/{id}', [AdminController::class, 'deleteEvent']); // Etkinliği sil

        Route::post('/create-club', [AdminController::class, 'createClub']); // Kulüp oluşturma islemi simdilik adminde.
        Route::get('/update-club/{id}', [AdminController::class, 'updateClub']); // Kulüp bilgilerini güncelle
        Route::post('/delete-club/{id}', [AdminController::class, 'deleteClub']); // Kulübü silme islemi simdilik adminde.

        Route::get('/deleted-clubs', [AdminController::class, 'deletedClubs']); // Silinmiş kulüpler
        Route::get('/deleted-events', [AdminController::class, 'deletedEvents']); // Silinmiş etkinlikler

        Route::post('/restore-event/{id}', [AdminController::class, 'restoreEvent']); // Etkinliği geri yükle
        Route::post('/restore-club/{id}', [AdminController::class, 'restoreClub']); // Kulübü geri yükle
    });


    // Middleware'larda kullanıcının bir kulüp yöneticisi olup olmadığını ve yöneticisi olduğu kulübün varlığını kontrol ediyoruz.

    Route::prefix('club-manager')->middleware('check.club-manager', 'check.club-manager-club')->group(function () {

        Route::post('/create-event', [ClubManagerController::class, 'createEvent']); // Etkinlik oluştur
        Route::post('/update-event/{id}', [ClubManagerController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::post('/delete-event/{id}', [ClubManagerController::class, 'deleteEvent']); // Etkinliği sil

        Route::get('/update-club/{id}', [ClubManagerController::class, 'updateClub']); // Kulüp bilgilerini güncelle

    });
});
