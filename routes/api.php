<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ClubManager;
use App\Http\Controllers\Guest;
use App\Http\Controllers\Guest\LoginController;

Route::get('/', function () {
    return response()->json(['message' => 'Buyrun burası API!']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [Guest\LoginController::class, 'register']);
    Route::post('/login', [Guest\LoginController::class, 'login']);
    Route::post('/logout', [Guest\LoginController::class, 'logout'])->middleware('auth:api');

    //mail atilma durumu kontrol edilmedi
    Route::post('/lost-password', [Guest\LoginController::class, 'lostPassword']);
    Route::post('/reset-password', [Guest\LoginController::class, 'resetPassword']);
    Route::post('/email/verify', [Guest\LoginController::class, 'verifyEmail'])->name('verification.verify');
    Route::get('/email/resend', [Guest\LoginController::class, 'resendEmail'])->name('verification.resend');
});


Route::middleware('auth:api')->group(function () {

    Route::get('/who-am-i', [User\UserController::class, 'whoAmI']); // oturum bilgileri kime ait?
    Route::get('/joined-clubs', [User\UserController::class, 'joinedClubs']); // oturum açan kullanıcının üye olduğu kulüpler
    Route::get('/joined-events', [User\UserController::class, 'joinedEvents']); // oturum açan kullanıcının dahil olduğu etkinlikler
    Route::get('/my-photo', [User\UserController::class, 'myPhoto']); // oturum açan kullanıcının profil fotoğrafı

    Route::prefix('user')->group(function () {

        Route::get('/all', [User\UserController::class, 'index']); // Kullanıcılar
        Route::get('/{id}', [User\UserController::class, 'show']); // Kullanıcı bilgileri

        Route::get('/{id}/clubs', [User\UserController::class, 'userClubs']); // Kullanıcının üye olduğu kulüpler
        Route::get('/{id}/events', [User\UserController::class, 'userEvents']); // Kullanıcının dahil olduğu etkinlikler

        Route::get('/{id}/photo', [User\UserController::class, 'userPhoto'])->name('getUserPhoto'); // Kullanıcının profil fotoğrafı

     
        Route::post('/join-club/{clubId}', [User\UserController::class, 'joinClub']); // Kulübe katıl
        Route::post('/leave-club/{clubId}', [User\UserController::class, 'leaveClub']); // Kulüpten ayrıl


        Route::post('/join-event/{eventId}', [User\UserController::class, 'joinEvent']); // Etkinliğe katıl
        Route::post('/leave-event/{eventId}', [User\UserController::class, 'leaveEvent']); // Etkinlikten ayrıl

        Route::post('/update-profile', [User\UserController::class, 'updateProfile']); // oturum açan kullanıcının profil bilgilerini güncelle
        Route::post('/update-password', [User\UserController::class, 'updatePassword']); // oturum açan kullanıcının şifresini güncelle

        Route::post('delete-photo', [User\UserController::class, 'deletePhoto']); // oturum açan kullanıcının profil fotoğrafını sil

    });

    Route::prefix('club')->group(function () {
        Route::get('/all', [User\ClubController::class, 'index']); // Kulüpler
        Route::get('/{id}', [User\ClubController::class, 'show']); // Kulüp bilgileri
        Route::get('/{id}/photo', [User\ClubController::class, 'clubPhoto'])->name('getClubPhoto'); // Kulübün profil fotoğrafı

        Route::get('/{id}/users', [User\ClubController::class, 'clubUsers']); // Kulübe üye olan kullanıcılar
        Route::get('/{id}/events', [User\ClubController::class, 'clubEvents']); // Kulübün etkinlikleri
    });

    Route::prefix('event')->group(function () {
        Route::get('/all', [User\EventController::class, 'index']); // Etkinlikler
        Route::get('/{id}', [User\EventController::class, 'show']); // Etkinlik bilgileri
        Route::get('/{id}/users', [User\EventController::class, 'eventUsers']); // Etkinliğe katılan kullanıcılar
        Route::get('/{id}/photo', [User\EventController::class, 'eventPhoto'])->name('getEventPhoto'); // Etkinliğin profil fotoğrafı
        Route::get('/{id}/club', [User\EventController::class, 'eventClub']); // Etkinliğin ait olduğu kulüp
    });


    Route::prefix('admin')->middleware('checkrole:admin')->group(function () {
        Route::get('/club-managers', [Admin\AdminController::class, 'clubManagers']); // Kulüp yöneticileri

        Route::post('/update-user-role/{id}', [Admin\AdminController::class, 'updateRole']); // Kullanıcının yetkisini değiştir
        Route::post('/delete-user/{id}', [Admin\AdminController::class, 'deleteUser']); // Kullanıcıyı sil
        Route::post('/restore-user/{id}', [Admin\AdminController::class, 'restoreUser']); // Kullanıcıyı geri yükle
        Route::get('/deleted-users', [Admin\AdminController::class, 'deletedUsers']); // Silinmiş kullanıcılar

        Route::post('/create-club', [Admin\AdminController::class, 'createClub']); // Kulüp oluşturma islemi simdilik adminde.
        Route::get('/update-club/{id}', [Admin\AdminController::class, 'updateClub']); // Kulüp bilgilerini güncelle
        Route::post('/delete-club/{id}', [Admin\AdminController::class, 'deleteClub']); // Kulübü silme islemi simdilik adminde.

        //yapilacaklar
        Route::post('/create-event', [Admin\AdminController::class, 'createEvent']); // Etkinlik oluştur
        Route::post('/update-event/{id}', [Admin\AdminController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::post('/delete-event/{id}', [Admin\AdminController::class, 'deleteEvent']); // Etkinliği sil



        Route::get('/deleted-clubs', [Admin\AdminController::class, 'deletedClubs']); // Silinmiş kulüpler
        Route::get('/deleted-events', [Admin\AdminController::class, 'deletedEvents']); // Silinmiş etkinlikler

        Route::post('/restore-event/{id}', [Admin\AdminController::class, 'restoreEvent']); // Etkinliği geri yükle
        Route::post('/restore-club/{id}', [Admin\AdminController::class, 'restoreClub']); // Kulübü geri yükle
    });


    // Middleware'larda kullanıcının bir kulüp yöneticisi olup olmadığını ve yöneticisi olduğu kulübün varlığını kontrol ediyoruz.

    Route::prefix('club-manager')->middleware('checkrole:club_manager', 'check.club.ownership')->group(function () {

        Route::post('/create-event', [ClubManager\ClubManagerController::class, 'createEvent']); // Etkinlik oluştur
        Route::post('/update-event/{id}', [ClubManager\ClubManagerController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::post('/delete-event/{id}', [ClubManager\ClubManagerController::class, 'deleteEvent']); // Etkinliği sil

        Route::get('/update-club/{id}', [ClubManager\ClubManagerController::class, 'updateClub']); // Kulüp bilgilerini güncelle
    });
});

//kategoriler cekilecek.
