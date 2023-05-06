<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::paginate(6);
        return $events;
    }

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

    public function eventUsers($eventId)
    {
        $users = Event::find($eventId)->users()->paginate(6);
        return response()->json($users, 200);
    }

    public function eventClub($eventId)
    {
        $club = Event::find($eventId)->club()->get();
        return response()->json($club, 200);
    }
    

  

    // public function eventClubs($eventId)
    // {
    //     $clubs = Event::find($eventId)->clubs()->paginate(6);
    //     return response()->json($clubs, 200);
    // }



}
