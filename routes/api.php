<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ClubManager;
use App\Http\Controllers\Guest;

Route::get('/', function () {
    return response()->json(['message' => 'Buyrun burası API!']);
});

Route::prefix('auth')->group(function () {
    Route::get('/auth/google/callback', [Guest\LoginController::class, 'handleGoogleCallback']);

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

        Route::patch('/update-profile', [User\UserController::class, 'updateProfile']); // oturum açan kullanıcının profil bilgilerini güncelle
        Route::patch('/update-password', [User\UserController::class, 'updatePassword']); // oturum açan kullanıcının şifresini güncelle

        Route::delete('delete-photo', [User\UserController::class, 'deletePhoto']); // oturum açan kullanıcının profil fotoğrafını sil

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

    Route::prefix('event-category')->group(function (){
        Route::get('/all', [User\EventCategoryController::class, 'index']);
        Route::get('/{id}', [User\EventCategoryController::class, 'show']);
        Route::get('/{id}/event/all', [User\EventCategoryController::class, 'eventCategoryEvents']);

        Route::get('/search/{name}', [User\EventCategoryController::class, 'searchEventCategory']);
    });


    // Middleware'larda kullanıcının bir kulüp yöneticisi olup olmadığını ve yöneticisi olduğu kulübün varlığını kontrol ediyoruz.
    Route::prefix('club-manager')->middleware('checkrole:club_manager', 'check.club.ownership')->group(function () {
        Route::get('/my-club', [ClubManager\ClubManagerController::class, 'myClub']); // Kulübüm
        Route::get('/my-club/user/all', [ClubManager\ClubManagerController::class, 'getClubMembers']); // Kulübüme üye olan kullanıcılar
        Route::get('/my-club/event/all', [ClubManager\ClubManagerController::class, 'myClubEvents']); // Kulübümün etkinlikleri

        Route::patch('/my-club/update', [ClubManager\ClubManagerController::class, 'updateClub']); // Yöneticisi olduğu kulüp bilgilerini güncelle
        Route::delete('/my-club/delete-photo', [ClubManager\ClubManagerController::class, 'deletePhoto']); // Kulübümün profil fotoğrafını sil

        Route::post('/create-event', [ClubManager\ClubManagerController::class, 'createEvent']); // Etkinlik oluştur
        Route::patch('/update-event/{id}', [ClubManager\ClubManagerController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::delete('/delete-event/{id}', [ClubManager\ClubManagerController::class, 'deleteEvent']); // Etkinliği sil
    });


    Route::prefix('admin')->middleware('checkrole:admin')->group(function () {
        Route::get('/user/all', [Admin\UserController::class, 'users']); // Kullanıcılar
        Route::get('/user/{id}', [Admin\UserController::class, 'user']); // Kullanıcı bilgileri

        Route::get('/club/all', [Admin\ClubController::class, 'clubs']); // Kulüpler
        Route::get('/club/{id}', [Admin\ClubController::class, 'club']); // Kulüp bilgileri

        Route::get('/club-manager/all', [Admin\ClubManagerController::class, 'clubManagers']); // Kulüp yöneticileri
        Route::get('/club-manager/{id}', [Admin\ClubManagerController::class, 'clubManager']); // Kulüp yöneticisi bilgileri

        Route::get('/event/all', [Admin\EventController::class, 'events']); // Etkinlikler
        Route::get('/event/{id}', [Admin\EventController::class, 'event']); // Etkinlik bilgileri

        Route::post('/create-user', [Admin\UserController::class, 'createUser']); // Kullanıcı oluştur
        Route::patch('/update-user/{id}', [Admin\UserController::class, 'updateUser']); // Kullanıcı bilgilerini güncelle
        Route::patch('/update-user-role/{id}', [Admin\UserController::class, 'updateRole']); // Kullanıcının yetkisini değiştir
        Route::delete('/delete-user/{id}', [Admin\UserController::class, 'deleteUser']); // Kullanıcıyı sil
        Route::get('/deleted-user/all', [Admin\UserController::class, 'deletedUsers']); // Silinmiş kullanıcılar
        Route::get('/deleted-user/{id}', [Admin\UserController::class, 'deletedUser']); // Silinmiş kullanıcılar
        Route::patch('/restore-user/{id}', [Admin\UserController::class, 'restoreUser']); // Kullanıcıyı geri yükle

        Route::post('/create-club', [Admin\ClubController::class, 'createClub']); // Kulüp oluştur
        Route::patch('/update-club/{id}', [Admin\ClubController::class, 'updateClub']); // Kulüp bilgilerini güncelle
        Route::delete('/delete-club/{id}', [Admin\ClubController::class, 'deleteClub']); // Kulübü sil
        Route::get('/deleted-club/all', [Admin\ClubController::class, 'deletedClubs']); // Silinmiş kulüpler
        Route::get('/deleted-club/{id}', [Admin\ClubController::class, 'deletedClub']); // Silinmiş kulüpler
        Route::patch('/restore-club/{id}', [Admin\ClubController::class, 'restoreClub']); // Kulübü geri yükle

        Route::post('/create-event', [Admin\EventController::class, 'createEvent']); // Etkinlik oluştur
        Route::patch('/update-event/{id}', [Admin\EventController::class, 'updateEvent']); // Etkinlik bilgilerini güncelle
        Route::delete('/delete-event/{id}', [Admin\EventController::class, 'deleteEvent']); // Etkinliği sil
        Route::get('/deleted-event/all', [Admin\EventController::class, 'deletedEvents']); // Silinmiş etkinlikler
        Route::patch('/restore-event/{id}', [Admin\EventController::class, 'restoreEvent']); // Etkinliği geri yükle

        Route::get('/event-category/all', [Admin\EventCategoryController::class, 'index']); // Etkinlik kategorileri
        Route::get('/event-category/{id}', [Admin\EventCategoryController::class, 'show']); // Etkinlik kategorisi bilgileri
        Route::post('/create-event-category', [Admin\EventCategoryController::class, 'store']); // Etkinlik kategorisi oluştur
        Route::patch('/update-event-category/{id}', [Admin\EventCategoryController::class, 'update']); // Etkinlik kategorisi bilgilerini güncelle
        Route::delete('/delete-event-category/{id}', [Admin\EventCategoryController::class, 'destroy']); // Etkinlik kategorisini sil
        Route::get('/deleted-event-category/all', [Admin\EventCategoryController::class, 'deletedEventCategories']); // Silinmiş etkinlik kategorileri
        Route::patch('/restore-event-category/{id}', [Admin\EventCategoryController::class, 'restore']); // Etkinlik kategorisini geri yükle
        
        Route::get('/event-category/search/{name}', [Admin\EventCategoryController::class, 'search']); // Etkinlik kategorisi ara
    });
});