<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminCreateEventRequest;
use App\Http\Requests\Admin\AdminUpdateEventRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Event;
use App\Models\Club;
use App\Models\User;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::paginate($this->getPerPage());
        $events->makeVisible($this->eventHiddens);
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $event->makeVisible($this->eventHiddens);
        return response()->json($event, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function createEvent(AdminCreateEventRequest $request): JsonResponse
    {
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
            return response()->json(['message' => 'Etkinlik başarıyla oluşturuldu.'], JsonResponse::HTTP_CREATED, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['message' => 'Etkinlik oluşturulamadı.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateEvent(AdminUpdateEventRequest $request, $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $event->name = $request->name;
        $event->title = $request->title;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->location = $request->location ?? $event->location;
        $event->image = $request->image ?? $event->image;
        $event->category_id = $request->category_id;
        $event->club_id = $request->club_id;
        $event->quota = $request->quota ?? $event->quota;
        $event->save();

        return response()->json(['message' => 'Etkinlik başarıyla güncellendi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteEvent($id): JsonResponse
    {
        $event = Event::withTrashed()->find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($event->trashed()) {
            return response()->json(['error' => 'Etkinlik zaten silinmiş.'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }
        $event->delete();
        return response()->json(['message' => 'Etkinlik başarıyla silindi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function restoreEvent($id): JsonResponse
    {
        $event = Event::withTrashed()->find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        if ($event->trashed()) {
            $event->restore();
            return response()->json(['message' => 'Etkinlik başarıyla geri yüklendi'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['error' => 'Etkinlik zaten aktif'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }
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
     * Get club of the event.
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventClub($eventId)
    {
        $club = Event::find($eventId)->club()->get();
        return response()->json($club, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get category of the event.
     * @param int $eventId
     * @return JsonResponse
     */
    public function eventCategory($eventId): JsonResponse
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $category = $event->category()->get();

        return response()->json($category, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }



    /**
     * Get all events of the club.
     * @param int $clubId
     * @return JsonResponse
     */
    public function clubEvents($clubId): JsonResponse
    {
        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $events = $club->events()->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get all events of the category.
     * @param int $categoryId
     * @return JsonResponse
     */
    public function categoryEvents($categoryId): JsonResponse
    {
        $events = Event::where('category_id', $categoryId)->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get all events of the user.
     * @param int $userId
     * @return JsonResponse
     */
    public function userEvents($userId)
    {
        $events = User::find($userId)->events()->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }


    public function deletedEvents()
    {
        $events = Event::onlyTrashed()->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deletedEvent($id)
    {
        $event = Event::onlyTrashed()->find($id);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $event->makeVisible($this->eventHiddens);
        return response()->json($event, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
