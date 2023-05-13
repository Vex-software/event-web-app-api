<?php

namespace App\Http\Controllers\ClubManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClubManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Event[]|Collection|Response
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $club = $user->managerOfClub;
        $events = $club->events;
        return response()->json([
            'events' => $events,
        ], 200);
    }

    /**
     * Create a new event.
     * @param Request $request
     * @return JsonResponse
     */
    public function createEvent(Request $request)
    {
        $user = auth()->user();
        $club = $user->managerOfClub;


        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'category_id' => 'required|integer',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $event = Event::create([
            'name' => $request->name,
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'image' => $request->image,
            'category_id' => $request->category_id,
            'club_id' => $club->id,
        ]);

        return response()->json([
            'message' => 'Etkinlik başarıyla oluşturuldu.',
            'event' => $event,
        ], 201);
    }

    public function updateEvent(Request $request, $id)
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'title' => 'string',
            'description' => 'string',
            'start_time' => 'date',
            'end_time' => 'date',
            'category_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($request->name) {
            $event->name = $request->name;
        }

        if ($request->title) {
            $event->title = $request->title;
        }

        if ($request->description) {
            $event->description = $request->description;
        }

        if ($request->start_time) {
            $event->start_time = $request->start_time;
        }

        if ($request->end_time) {
            $event->end_time = $request->end_time;
        }

        if ($request->location) {
            $event->location = $request->location;
        }

        if ($request->image) {
            $event->image = $request->image;
        }

        if ($request->category_id) {
            $event->category_id = $request->category_id;
        }

        $event->save();

        return response()->json([
            'message' => 'Etkinlik başarıyla güncellendi.',
            'event' => $event,
        ], 200);
    }
}
