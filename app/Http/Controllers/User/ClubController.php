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
     * @return Club[]|Collection|Response
     */
    public function index()
    {
        $clubs = Club::paginate(6);
        $clubs->load('manager', 'users', 'events');
        return response()->json($clubs, 200);
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return Response
     */
    public function show(int $id): JsonResponse
    {
        $club = Club::findOrFail($id);
        $club->load('users');
        $club->load('manager');
        $club->load('events');
        return response()->json([
            'club' => $club,
        ], 200);
    }

    /**
     * Get users of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubUsers(int $clubId): JsonResponse
    {
        $club = Club::findOrFail($clubId);
        $clubUsers = $club->users()->paginate(6);
        return response()->json($clubUsers, 200);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubEvents(int $clubId): JsonResponse
    {
        $club = Club::findOrFail($clubId);
        $clubEvents = $club->events()->paginate(6);
        return response()->json($clubEvents, 200);
    }

    public function clubPhoto($id)
    {
        $query = DB::table('clubs')->select('logo')->where('id', $id)->get();

        if ($query->count() <= 0) {
            return abort(404);
        }
        $path = $query[0]->logo;


        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                abort(404);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }
}
