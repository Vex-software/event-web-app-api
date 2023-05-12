<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

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
    public function createEvent(Request $request): JsonResponse
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'date',
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
}
