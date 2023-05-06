<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;


class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(6);
        return $users;
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $userClubs = $user->clubs()->paginate(6);
        return response()->json([
            'user' => $user,
            'userClubs' => $userClubs
        ], 200);
    }

    public function userClubs($userId)
    {
        $clubs = User::find($userId)->clubs()->paginate(6);
        return response()->json($clubs, 200);
    }

    public function joinClub(string $clubId)
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kulüp yok ise 404 döndür
        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kullanıcı zaten kulüpte ise 200 döndür
        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten kulüpte'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->attach($user->id);
            return response()->json(['success' => 'Kullanıcı Kulübe katıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function leaveClub(string $clubId)
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu kulüpte değil'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->detach($user->id);
            return response()->json(['success' => 'Kullanıcı Kulüpten ayrıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function joinEvent(string $eventId)
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Etkinlik yok ise 404 döndür
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kullanıcı zaten etkinlikte ise 200 döndür
        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten etkinlikte'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->attach($user->id);
            return response()->json(['success' => 'Kullanıcı Etkinliğe katıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function leaveEvent(string $eventId)
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu etkinlikte değil'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->detach($user->id);
            return response()->json(['success' => 'Kullanıcı Etkinlikten ayrıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function userEvents($userId)
    {
        $events = User::find($userId)->events()->paginate(6);
        return response()->json($events, 200);
    }

    public function myClubs()
    {
        $user = User::find(auth()->user()->id);
        $clubs = $user->clubs()->paginate(6);
        
        return response()->json($clubs, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function myEvents(){
        $user = User::find(auth()->user()->id);
        $events = $user->events()->paginate(6);
        
        return response()->json($events, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function whoAmI()
    {
        $user = auth()->user();
        return response()->json(['user' => $user], 200, [], JSON_UNESCAPED_UNICODE);
    }

    
}
