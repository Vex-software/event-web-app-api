<?php

namespace App\Http\Controllers\ClubManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ClubResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClubManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Event[]|Collection|Response
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $club = $user->managerOfClub;
        $events = $club->events;

        $transformedEvents = EventResource::collection($events);

        return response()->json($transformedEvents, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Create a new event.
     *
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
            'end_time' => 'nullable|date',
            'location' => 'required|string',
            'category_id' => 'required|integer',
            'image' => 'nullable|string',
            'quota' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $event = new Event();
        $event->name = $request->name;
        $event->title = $request->title;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        if ($request->end_time) {
            $event->end_time = $request->end_time;
        }
        $event->location = $request->location;
        if ($request->image) {
            $event->image = $request->image;
        }
        if ($request->quota) {
            $event->quota = $request->quota;
        }
        $event->category_id = $request->category_id;
        $event->club_id = $club->id;
        $event->save();

        return response()->json([
            'message' => 'Etkinlik başarıyla oluşturuldu.',
            'event' => $event,
        ], 201);
    }

    public function updateEvent(Request $request, $id)
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Kulüp Bulunamadı',
            ], 403);
        }

        if ($event->club_id != $club->id) {
            return response()->json([
                'message' => 'Bu kulübe ait bir etkinlik değil.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'location' => 'required|string',
            'category_id' => 'required|integer',
            'image' => 'nullable|string',
            'quota' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $event->name = $request->name;
        $event->title = $request->title;
        $event->description = $request->description;
        $event->start_time = $request->start_time;
        if ($request->end_time) {
            $event->end_time = $request->end_time;
        }
        $event->location = $request->location;
        if ($request->image) {
            $event->image = $request->image;
        }
        if ($request->quota) {
            $event->quota = $request->quota;
        }
        $event->category_id = $request->category_id;
        $event->club_id = $club->id;
        $event->save();

        return response()->json([
            'message' => 'Etkinlik başarıyla güncellendi.',
            'event' => $event,
        ], 200);
    }

    public function myClub()
    {
        $user = auth()->user();
        $club = $user->managerOfClub->load('users', 'events');

        $transformedClub = new ClubResource($club);

        return response()->json($transformedClub, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function myClubMembers(SearchRequest $request)
    {
        $paginate = $request->paginate ?? $this->getPerPage();
        $user = auth()->user();
        $club = $user->managerOfClub;

        $users = $club->users()->paginate($paginate);

        $users->getCollection()->transform(function ($user) {
            return new UserResource($user);
        });

        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function myClubEvents(SearchRequest $request)
    {
        $paginate = $request->paginate ?? $this->getPerPage();
        $user = auth()->user();
        $club = $user->managerOfClub;

        $events = $club->events()->paginate($paginate);

        $events->getCollection()->transform(function ($event) {
            return new EventResource($event);
        });

        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Etkinlik başarıyla silindi.',
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteClubMember($id)
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        $member = User::findOrFail($id);

        if ($member->club_id != $club->id) {
            return response()->json([
                'message' => 'Bu kulübe ait bir üye değil.',
            ], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }

        $member->club_id = null;
        $member->save();

        return response()->json([
            'message' => 'Üye başarıyla kulüpten çıkarıldı.',
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateClub(Request $request)
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name,'.$club->id],
            'title' => 'required|string',
            'description' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:clubs,email,'.$club->id],
            'phone_number' => ['required', 'string', 'max:255', 'unique:clubs,phone_number,'.$club->id],
            'address' => ['required', 'string', 'max:255', 'unique:clubs,address,'.$club->id],
            'website' => 'nullable',
            'founded_year' => 'nullable|date',
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if (request()->hasFile('logo')) {
            $file = request()->file('logo');
            if (Storage::exists($club->logo)) {
                Storage::delete($club->logo);
            }
            $path = $file->store('public/clubs');
            $club->logo = $path;
            $club->save();
        }

        $club->name = request()->name;
        $club->description = request()->description;
        $club->email = request()->email;
        $club->phone = request()->phone;
        $club->location = request()->location;
        if (request()->logo) {
            $club->logo = request()->logo;
        }
        if (request()->facebook) {
            $club->facebook = request()->facebook;
        }
        if (request()->twitter) {
            $club->twitter = request()->twitter;
        }
        if (request()->instagram) {
            $club->instagram = request()->instagram;
        }
        if (request()->website) {
            $club->website = request()->website;
        }
        $club->save();

        return response()->json([
            'message' => 'Kulüp başarıyla güncellendi.',
            'club' => $club,
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deletePhoto()
    {
        $user = auth()->user();
        $club = $user->managerOfClub;

        if (Storage::exists($club->logo)) {
            Storage::delete($club->logo);
        }
        $club->logo = null;
        $club->save();

        return response()->json([
            'message' => 'Kulüp logosu başarıyla silindi.',
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
