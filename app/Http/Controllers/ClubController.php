<?php

namespace App\Http\Controllers;

use App\Models\Club;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Club[]|Collection|Response
     */
    public function index(): Collection
    {
        $clubs = Club::paginate(6);
        return $clubs;
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id): JsonResponse
    {
        $club = Club::findOrFail($id);
        $clubUsers = $club->users()->paginate(6); // 10 katılımcı için pagination yapılıyor
        return response()->json([
            'club' => $club,
            'clubUsers' => $clubUsers
        ], 200);
    }

    /**
     * Get users of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubUsers($clubId): JsonResponse
    {
        $users = Club::find($clubId)->users()->paginate(6);
        return response()->json($users, 200);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubEvents($clubId): JsonResponse
    {
        // $club = Club::find(1);

        // $event = $club->events()->create([
        //     'name' => 'Örnek Etkinlik 2',
        //     'description' => 'Bu bir örnek etkinlik. 2',
        //     'start_time' => '2023-05-20 18:00:00',
        //     'end_time' => '2023-05-20 21:00:00',
        //     // 'category_id' => 1
        // ]);

        $events = Club::find($clubId)->events()->paginate(6);
        return response()->json($events, 200);
    }
}
