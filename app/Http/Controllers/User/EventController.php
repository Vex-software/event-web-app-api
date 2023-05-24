<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $events = Event::paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $event = Event::findOrFail($id)->load('category', 'club', 'users')->loadCount('users');
        return response()->json($event, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get club of the event.
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventClub($eventId): JsonResponse
    {
        $club = Event::find($eventId)->club()->first();
        return response()->json($club, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get joined users of the event.
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventUsers($eventId): JsonResponse
    {
        $users = Event::find($eventId)->users()->paginate($this->getPerPage());
        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get event photo.
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventPhoto($id)
    {
        $path = DB::table('events')->select('image')->where('id', $id)->get();

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    // Henüz kullanılmıyor.
    // public function eventComments($eventId)
    // {
    //     $comments = Event::find($eventId)->comments()->paginate(6);
    //     return response()->json($comments, 200);
    // }
}
