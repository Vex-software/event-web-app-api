<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ClubResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @throws \Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $query = new Club();
        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query = $query->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('description', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('title', 'like', '%'.$searchKeyword.'%');
            });
        }

        $order = $request->input('order', 'asc');

        if ($request->has('orderBy')) {
            $orderBy = $request->input('orderBy');
            $validColumns = ['name', 'created_at', 'updated_at'];

            if (in_array($orderBy, $validColumns)) {
                $clubs = $query->orderBy($orderBy, $order)->paginate($this->getPerPage());
            } else {
                $clubs = $query->paginate($this->getPerPage());
            }
        } else {
            $clubs = $query->paginate($this->getPerPage());
        }

        $clubs->getCollection()->transform(function ($club) {
            return (new ClubResource($club))->toArray(request());
        });

        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function show(int $id): JsonResponse
    {
        $club = Club::find($id);
        if (! $club) {
            return response()->json(['message' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json(new ClubResource($club), JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get users of the club.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function clubUsers(int $clubId, SearchRequest $request): JsonResponse
    {
        $club = Club::find($clubId);
        if (! $club) {
            return response()->json(['message' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $query = $club->users();

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where('name', 'like', '%'.$searchKeyword.'%');
        }

        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy');

        if (in_array($orderBy, ['name', 'created_at', 'updated_at'])) {
            $query->orderBy($orderBy, $order);
        }

        $perPage = $this->getPerPage();

        $users = $query->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            return (new UserResource($user))->toArray(request());
        });

        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get events of the club.
     *
     * @throws \Exception
     */
    public function clubEvents(int $clubId, SearchRequest $request): JsonResponse
    {
        $club = Club::findOrFail($clubId);

        $query = $club->events();

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('title', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('description', 'like', '%'.$searchKeyword.'%');
            });
        }

        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy');

        if (in_array($orderBy, ['name', 'start_time', 'end_time'])) {
            $query->orderBy($orderBy, $order);
        }

        $perPage = $this->getPerPage();
        $clubEvents = $query->paginate($perPage);

        $transformedEvents = EventResource::collection($clubEvents);

        if (count($transformedEvents) < 1) {
            return response()->json(['message' => 'Kulübe ait etkinlik bulunamadı.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($transformedEvents, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get photo of the club.
     *
     * @param  int  $id
     */
    public function clubPhoto($id)
    {
        $path = DB::table('clubs')->select('logo')->where('id', $id)->get();

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $type = get_headers($path, 1)['Content-Type'];

            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, JsonResponse::HTTP_OK, ['Content-Type' => $type]);
        } else {
            if (! File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);

            return response()->file($path, ['Content-Type' => $type]);
        }
    }
}
