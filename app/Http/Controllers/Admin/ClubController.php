<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\Club;
use Illuminate\Http\JsonResponse;

class ClubController extends Controller
{
    public function createClub(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'logo' => 'nullable',
            'email' => 'required|email',
            'phone_number' => 'required',
            'website' => 'nullable',
            'founded_year' => 'nullable|date',
            'manager_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $club = new Club();
        $club->name = $request->name;
        $club->title = $request->title;
        $club->description = $request->description;
        if ($request->logo) {
            $club->logo = $request->logo;
        }
        $club->email = $request->email;
        $club->phone_number = $request->phone_number;
        if ($request->website) {
            $club->website = $request->website;
        }
        if ($request->founded_year) {
            $club->founded_year = $request->founded_year;
        }
        $club->manager_id = $request->manager_id;
        $club->save();

        return response()->json(['message' => 'Kulüp başarıyla oluşturuldu.'], 200);
    }

    public function updateClub(Request $request, $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'logo' => 'nullable',
            'email' => 'required|email',
            'phone_number' => 'required',
            'website' => 'nullable',
            'founded_year' => 'nullable|date',
            'manager_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $club->name = $request->name;
        $club->title = $request->title;
        $club->description = $request->description;
        if ($request->logo) {
            $club->logo = $request->logo;
        }
        $club->email = $request->email;
        $club->phone_number = $request->phone_number;
        if ($request->website) {
            $club->website = $request->website;
        }
        if ($request->founded_year) {
            $club->founded_year = $request->founded_year;
        }
        $club->manager_id = $request->manager_id;
        $club->save();

        return response()->json(['message' => 'Kulüp başarıyla güncellendi.'], 200);
    }

    public function deleteClub($id): JsonResponse
    {
        $club = Club::withTrashed()->findOrFail($id);

        if ($club->trashed()) {
            return response()->json(['error' => 'Kulüp zaten silinmiş.'], 400);
        }
        $club->delete();
        return response()->json(['message' => 'Kulüp başarıyla silindi.'], 200);
    }

    public function restoreClub($id): JsonResponse
    {
        $club = Club::withTrashed()->findOrFail($id);

        if (!$club->trashed()) {
            return response()->json(['error' => 'Kulüp zaten silinmemiş.'], 400);
        }
        $club->restore();
        return response()->json(['message' => 'Kulüp başarıyla geri yüklendi.'], 200);
    }

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
    public function show(int $id): JsonResponse
    {
        $club = Club::findOrFail($id);
        $clubUsers = $club->users()->paginate(6);
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
    public function clubUsers(int $clubId): JsonResponse
    {
        $users = Club::find($clubId)->users()->paginate(6);
        return response()->json($users, 200);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return Response
     */
    public function clubEvents(int $clubId): JsonResponse
    {
        $events = Club::find($clubId)->events()->paginate(6);
        return response()->json($events, 200);
    }
}
