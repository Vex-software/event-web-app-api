<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Club;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:admin');
    // }

  

    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
    }


    public function deleteClub($id)
    {
        $club = Club::findOrFail($id);
        $club->delete();
        return response()->json(['message' => 'Kulüp başarıyla silindi.'], 200);
    }

    public function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Etkinlik başarıyla silindi.'], 200);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Kullanıcı başarıyla silindi.'], 200);
    }
}
