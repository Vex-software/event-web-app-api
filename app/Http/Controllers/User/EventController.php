<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ClubResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $query = new Event();

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query = $query->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('title', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('description', 'like', '%'.$searchKeyword.'%');
            });
        }
        $order = $request->input('order', 'asc');

        if ($request->has('orderBy')) {
            $orderBy = $request->input('orderBy');
            $validColumns = ['name', 'start_time', 'end_time'];

            if (in_array($orderBy, $validColumns)) {
                $events = $query->orderBy($orderBy, $order)->paginate($this->getPerPage());
            } else {
                $events = $query->paginate($this->getPerPage());
            }
        } else {
            $events = $query->paginate($this->getPerPage());
        }

        $events->getCollection()->transform(function ($event) {
            return (new EventResource($event))->toArray(request());
        });

        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     * Not : Event tablosunda hiçbir alan kullanıcıya gizli olmadığı için burada model metodu kullanılmadı.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse
    {
        $event = Event::find($id);
        if (! $event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(new EventResource($event), JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get club of the event.
     *
     * @param  int  $eventId
     */
    public function eventClub($eventId, SearchRequest $request): JsonResponse
    {
        $query = Event::find($eventId)->club();
        if (! $query) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where('name', 'like', '%'.$searchKeyword.'%')
                ->orWhere('description', 'like', '%'.$searchKeyword.'%');
        }

        if ($request->has('orderBy')) {
            $orderBy = $request->input('orderBy');
            $validColumns = ['name', 'created_at', 'updated_at'];

            if (in_array($orderBy, $validColumns)) {
                $order = $request->input('order', 'asc');
                $query->orderBy($orderBy, $order);
            }
        }

        $club = $query->first();
        $clubResource = new ClubResource($club);

        return response()->json($clubResource->toArray(request()), JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get joined users of the event.
     *
     * @param  int  $eventId
     */
    public function eventUsers($eventId, SearchRequest $request): JsonResponse
    {
        $query = Event::find($eventId)->users();
        if (! $query) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where('name', 'like', '%'.$searchKeyword.'%');
        }

        if ($request->has('orderBy')) {
            $orderBy = $request->input('orderBy');
            $validColumns = ['name', 'created_at', 'updated_at'];

            if (in_array($orderBy, $validColumns)) {
                $order = $request->input('order', 'asc');
                $query->orderBy($orderBy, $order);
            }
        }

        $perPage = $request->input('paginate', $this->getPerPage());
        $users = $query->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            return (new UserResource($user))->toArray(request());
        });

        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function eventPhoto($id, Request $request)
    {

        $path = DB::table('events')->where('id', $id)->value('image');

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $type = get_headers($path, 1)['Content-Type'];

            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (! File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);

            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    // Henüz kullanılmıyor.
    // public function eventComments($eventId)
    // {
    //     $comments = Event::find($eventId)->comments()->paginate(6);
    //     return response()->json($comments, 200);
    // }
}
