<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Event[]|Collection|Response
     */
    public function index() : Collection
    {
        $events = Event::paginate(6);
        return $events;
    }

    /**
     * Display the specified resource.
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
     * Get joined users of the event.
     * @param int $eventId
     * @return Response
     */
    public function eventUsers($eventId)
    {
        $users = Event::find($eventId)->users()->paginate(6);
        return response()->json($users, 200);
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
    

    /* Henüz kullanılmayan fonksiyonlar */ 
    
    // /**
    //  * Get clubs of the event.
    //  * @param int $eventId
    //  * @return Response
    //  */
    // public function eventClubs($eventId)
    // {
    //     $clubs = Event::find($eventId)->clubs()->paginate(6);
    //     return response()->json($clubs, 200);
    // }



}
