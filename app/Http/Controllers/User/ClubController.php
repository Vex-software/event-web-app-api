<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $clubs = Club::paginate($this->getPerPage());
        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function show(int $id): JsonResponse
    {
        $club = Club::findOrFail($id);
        $club->load('users', 'manager', 'events');
        return response()->json($club, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get users of the club.
     * @param int $clubId
     * @return Response
     * @throws \Exception
     */
    public function clubUsers(int $clubId): JsonResponse
    {
        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['message' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND);
        }
        $clubUsers = $club->users();
        return response()->json($clubUsers, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return JsonResponse
     * @throws \Exception
     */
    public function clubEvents(int $clubId): JsonResponse
    {
        $club = Club::findOrFail($clubId);
        $clubEvents = $club->events()->paginate($this->getPerPage());
        return response()->json($clubEvents, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get photo of the club.
     * @param int $id
     */
    public function clubPhoto($id)
    {
        $path = DB::table('clubs')->select('logo')->where('id', $id)->get();

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }
}
