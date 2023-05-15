<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use App\Models\Event;

class EventController extends Controller
{

    protected $eventHiddens = [
        'created_at',
        'updated_at',
        'deleted_at',
        'pivot',
    ];


    //    /**
    //  * Get all events.
    //  * @return Response
    //  */
    // public function events()
    // {
    //     $events = Event::withTrashed()->paginate(10);
    //     return response()->json($events, 200);
    // }


    public function events()
    {
        $events = Event::paginate(10);
        $events->makeVisible($this->eventHiddens);
        return response()->json($events, 200);
    }

    public function event($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], 404);
        }
        $event->makeVisible($this->eventHiddens);
        return response()->json($event, 200);
    }

    public function createEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'club_id' => 'required|integer',
            'category_id' => 'required|integer',
            'location' => 'required|string',
            'image' => 'nullable',
            'quota' => 'nullable|integer',
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
            'club_id' => $request->club_id,
        ]);
        if ($event) {
            return response()->json(['message' => 'Etkinlik başarıyla oluşturuldu.'], 200);
        } else {
            return response()->json(['message' => 'Etkinlik oluşturulamadı.'], 400);
        }
    }

    public function updateEvent(Request $request, $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'club_id' => 'required|integer',
            'category_id' => 'required|integer',
            'location' => 'nullable|string',
            'image' => 'nullable',
            'quota' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $event->name = $request->name;
        $event->title = $request->title;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        if ($request->location) {
            $event->location = $request->location;
        }
        if ($request->image) {
            $event->image = $request->image;
        }
        $event->category_id = $request->category_id;
        $event->club_id = $request->club_id;
        if ($request->quota) {
            $event->quota = $request->quota;
        }
        $event->save();

        return response()->json(['message' => 'Etkinlik başarıyla güncellendi.'], 200);
    }

    public function deleteEvent($id): JsonResponse
    {
        $event = Event::withTrashed()->findOrFail($id);

        if ($event->trashed()) {
            return response()->json(['error' => 'Etkinlik zaten silinmiş.'], 400);
        }
        $event->delete();
        return response()->json(['message' => 'Etkinlik başarıyla silindi.'], 200);
    }

    public function restoreEvent($id): JsonResponse
    {
        $event = Event::withTrashed()->find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], 404);
        }

        if ($event->deleted_at === null) {
            return response()->json(['error' => 'Etkinlik zaten aktif'], 400);
        }

        $event->restore();
        return response()->json(['message' => 'Etkinlik başarıyla geri yüklendi'], 200);
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

    /**
     * Get category of the event.
     * @param int $eventId
     * @return Response
     */
    public function eventCategory($eventId)
    {
        $category = Event::find($eventId)->category()->get();
        return response()->json($category, 200);
    }



    /**
     * Get all events of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubEvents($clubId)
    {
        $events = Club::find($clubId)->events()->paginate(6);
        return response()->json($events, 200);
    }

    /**
     * Get all events of the category.
     * @param int $categoryId
     * @return Response
     */
    public function categoryEvents($categoryId)
    {
        $events = Event::where('category_id', $categoryId)->paginate(6);
        return response()->json($events, 200);
    }

    /**
     * Get all events of the user.
     * @param int $userId
     * @return Response
     */
    public function userEvents($userId)
    {
        $events = User::find($userId)->events()->paginate(6);
        return response()->json($events, 200);
    }


    public function deletedEvents()
    {
        $events = Event::onlyTrashed()->paginate(6);
        $events->makeVisible($this->eventHiddens);
        return response()->json($events, 200);
    }

    public function deletedEvent($id)
    {
        $event = Event::onlyTrashed()->findOrFail($id);
        $event->makeVisible($this->eventHiddens);
        return response()->json($event, 200);
    }

    // /**
    //  * Get all events of the user.
    //  * @param int $userId
    //  * @return Response
    //  */
    // public function userJoinedEvents($userId)
    // {
    //     $events = User::find($userId)->joinedEvents()->paginate(6);
    //     return response()->json($events, 200);
    // }
}
