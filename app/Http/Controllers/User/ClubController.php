<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use Illuminate\Http\JsonResponse;


class ClubController extends Controller
{
    protected $hiddenClubFields = ['phone_number', 'email', 'created_at', 'updated_at', 'deleted_at'];
    protected $hiddenUserFields = ['email', 'phone_number', 'address', 'city_id', 'email_verified_at', 'google_id', 'github_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Display a listing of the resource.
     * @return Club[]|Collection|Response
     */
    public function index()
    {
        $clubs = Club::getAllClubDataForUser(6);
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
        $data = Club::getClubData($club, $clubUsers = true, $clubManager = true, $clubEvents = true, $paginate = 6);
        return response()->json($data, 200);
    }

    /**
     * Get users of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubUsers(int $clubId): JsonResponse
    {
        $club = Club::findOrFail($clubId);
        $users = Club::getClubData($club, $clubUsers = true, $clubManager = false, $clubEvents = false, $paginate = 6)->users;
        return response()->json($users, 200);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubEvents(int $clubId): JsonResponse
    {
        $club = Club::findOrFail($clubId);
        $events = Club::getClubData($club, $clubUsers = false, $clubManager = false, $clubEvents = true, $paginate = 6)->events;
        return response()->json($events, 200);
    }
}
