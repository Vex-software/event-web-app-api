<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Validator;



class ClubManagerController extends Controller
{
    public function createEvent(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Yetkisiz istek'], 401);
        }
        $club = $user->managerOfClub;
        if (!$club) {
            return response()->json(['error' => 'Kulüp yöneticisi değilsiniz'], 403);
        }
    
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
