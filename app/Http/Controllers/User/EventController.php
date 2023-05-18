<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     * @return Event[]|Collection|Response
     */
    public function index()
    {
        $events = Event::paginate(6)->load('category', 'club', 'users')->loadCount('users');
        return $events;
    }

    /**
     * Display the specified resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $event = Event::findOrFail($id)->load('category', 'club', 'users')->loadCount('users');
        return response()->json([
            'event' => $event
        ], 200);
    }


    /**
     * Get club of the event.
     * @param int $eventId
     * @return Response
     */
    public function eventClub($eventId)
    {
        $club = Event::find($eventId)->club()->first();
        return response()->json($club, 200);
    }



    public function eventUsers($eventId)
    {
        $users = Event::find($eventId)->users()->paginate(6);
        return response()->json($users, 200);
    }


    public function eventPhoto($id)
    {
        $query = DB::table('events')->select('image')->where('id', $id)->get();

        if ($query->count() <= 0) {
            return abort(404);
        }

        $path = $query[0]->image;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                abort(404);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    // public function eventComments($eventId)
    // {
    //     $comments = Event::find($eventId)->comments()->paginate(6);
    //     return response()->json($comments, 200);
    // }

}
