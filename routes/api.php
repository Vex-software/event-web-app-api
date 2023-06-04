<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ClubManager;
use App\Http\Controllers\Guest;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Buyrun burası API!. Lütfen /api/v1/ ile başlayan rotaları kullanın. :)']);
});

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Buyrun burası API v1!. Hadi bakalım hayırlısı.']);
    });

    Route::prefix('auth')->group(function () {
        // Route::get('/google/callback', [Guest\AuthController::class, 'handleGoogleCallback']);
        Route::post('/register', [Guest\AuthController::class, 'register']);
        Route::post('/login', [Guest\AuthController::class, 'login']); //requeste bakarsin buralarda
        Route::post('/logout', [Guest\AuthController::class, 'logout'])->middleware('auth:api');

        Route::get('/email/verify', [Guest\AuthController::class, 'verifyEmail'])->name('verify-email');
        Route::post('/password/forgot-password', [Guest\AuthController::class, 'lostPassword']);
        Route::get('/password/reset', [Guest\AuthController::class, 'resetPassword'])->name('password.reset');
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('/who-am-i', [User\UserController::class, 'whoAmI']);
        Route::get('/joined-club/all', [User\UserController::class, 'joinedClubs']);
        Route::get('/joined-event/all', [User\UserController::class, 'joinedEvents']);
        Route::get('/my-photo', [User\UserController::class, 'myPhoto']);

        Route::post('/join-club/{clubId}', [User\UserController::class, 'joinClub']);
        Route::post('/leave-club/{clubId}', [User\UserController::class, 'leaveClub']);
        Route::post('/join-event/{eventId}', [User\UserController::class, 'joinEvent']);
        Route::post('/leave-event/{eventId}', [User\UserController::class, 'leaveEvent']);

        Route::patch('/update-profile', [User\UserController::class, 'updateProfile']);
        Route::patch('/update-password', [User\UserController::class, 'updatePassword']);
        Route::delete('delete-photo', [User\UserController::class, 'deletePhoto']);

        Route::prefix('user')->group(function () {
            Route::get('/all', [User\UserController::class, 'index']);
            Route::get('/{id}', [User\UserController::class, 'show']);

            Route::get('/{id}/club/all', [User\UserController::class, 'userClubs']);
            Route::get('/{id}/event/all', [User\UserController::class, 'userEvents']);

            Route::get('/{id}/photo', [User\UserController::class, 'userPhoto'])->name('getUserPhoto')->withoutMiddleware('auth:api');
        });

        Route::prefix('club')->group(function () {
            Route::get('/all', [User\ClubController::class, 'index']);
            Route::get('/{id}', [User\ClubController::class, 'show']);
            Route::get('/{id}/photo', [User\ClubController::class, 'clubPhoto'])->name('getClubPhoto');

            Route::get('/{id}/user/all', [User\ClubController::class, 'clubUsers']);
            Route::get('/{id}/event/all', [User\ClubController::class, 'clubEvents']);
        });

        Route::prefix('event')->group(function () {
            Route::get('/all', [User\EventController::class, 'index']); // Etkinlikler
            Route::get('/{id}', [User\EventController::class, 'show']); // Etkinlik bilgileri
            Route::get('/{id}/user/all', [User\EventController::class, 'eventUsers']); // Etkinliğe katılan kullanıcılar
            Route::get('/{id}/photo', [User\EventController::class, 'eventPhoto'])->name('getEventPhoto')->withoutMiddleware('auth:api'); // Etkinliğin profil fotoğrafı
            Route::get('/{id}/club', [User\EventController::class, 'eventClub']); // Etkinliğin ait olduğu kulüp
        });

        Route::prefix('event-category')->group(function () {
            Route::get('/all', [User\EventCategoryController::class, 'index']);
            Route::get('/{id}', [User\EventCategoryController::class, 'show']);
            Route::get('/{id}/event/all', [User\EventCategoryController::class, 'eventCategoryEvents']);

            Route::get('/search/{name}', [User\EventCategoryController::class, 'searchEventCategory']);
        });

        Route::prefix('club-manager')->middleware(['checkrole:club_manager', 'check.club.ownership'])->group(function () {
            Route::get('/my-club', [ClubManager\ClubManagerController::class, 'myClub']);
            Route::get('/my-club/user/all', [ClubManager\ClubManagerController::class, 'myClubMembers']);
            Route::get('/my-club/event/all', [ClubManager\ClubManagerController::class, 'myClubEvents']);
            Route::patch('/my-club/update', [ClubManager\ClubManagerController::class, 'updateClub']);
            Route::delete('/my-club/delete-photo', [ClubManager\ClubManagerController::class, 'deletePhoto']);

            Route::post('/create-event', [ClubManager\ClubManagerController::class, 'createEvent']);
            Route::patch('/update-event/{id}', [ClubManager\ClubManagerController::class, 'updateEvent']);
            Route::delete('/delete-event/{id}', [ClubManager\ClubManagerController::class, 'deleteEvent']);
        });

        Route::prefix('admin')->middleware('checkrole:admin')->group(function () {
            Route::get('/user/all', [Admin\UserController::class, 'index']); // Kullanıcılar
            Route::get('/user/{id}', [Admin\UserController::class, 'show']); // Kullanıcı bilgileri

            Route::get('/club/all', [Admin\ClubController::class, 'index']); // Kulüpler
            Route::get('/club/{id}', [Admin\ClubController::class, 'show']); // Kulüp bilgileri

            Route::get('/club-manager/all', [Admin\ClubManagerController::class, 'index']); // Kulüp yöneticileri
            Route::get('/club-manager/{id}', [Admin\ClubManagerController::class, 'show']); // Kulüp yöneticisi bilgileri

            Route::get('/event/all', [Admin\EventController::class, 'index']); // Etkinlikler
            Route::get('/event/{id}', [Admin\EventController::class, 'show']); // Etkinlik bilgileri

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
});
