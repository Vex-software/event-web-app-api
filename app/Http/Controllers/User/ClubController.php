<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use Illuminate\Http\JsonResponse;


class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Club[]|Collection|Response
     */
    public function index()
    {
        $clubs = Club::paginate(6);
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
        $clubUsers = $club->users()->paginate(6);
        $clubManager = $club->manager()->get();
        $clubEvents = $club->events()->paginate(6);
        return response()->json([
            'club' => $club,
            'clubUsers' => $clubUsers,
            'clubManager' => $clubManager,
            'clubEvents' => $clubEvents
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
}
