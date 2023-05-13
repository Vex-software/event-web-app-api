<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
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
        $events = Event::paginate(6);
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
        $event = Event::findOrFail($id);
        $eventUsers = $event->users()->paginate(6);
        $eventClub = $event->club()->get();
        return response()->json([
            'event' => $event,
            'eventUsers' => $eventUsers,
            'eventClub' => $eventClub
        ], 200);
    }


    /**
     * Get club of the event.
     * @param int $eventId
     * @return Response
     */
    public function eventClub($eventId)
    {
        $club = Event::find($eventId)->club()->get();
        return response()->json($club, 200);
    }

    public function eventUsers($eventId)
    {
        $users = Event::find($eventId)->users()->paginate(6);
        return response()->json($users, 200);
    }

}
