<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminUpdateClubRequest;
use App\Http\Requests\Admin\AdminCreateClubRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Club;
use Carbon\Carbon;

class ClubController extends Controller
{
    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $clubs = Club::paginate($this->getPerPage());
        $clubs->makeVisible($this->clubHiddens);
        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get club by id
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function show($id): JsonResponse
    {
        $club = Club::find($id);
        $club->makeVisible($this->clubHiddens);
        $club->load('users', 'events');
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($club, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param AdminCreateClubRequest $request
     * @return JsonResponse
     */
    public function createClub(AdminCreateClubRequest $request): JsonResponse
    {
        $club = new Club();
        $club->name = $request->name;
        $club->title = $request->title ?? "";
        $club->description = $request->description ?? "";
        $club->email = $request->email;
        $club->phone_number = $request->phone_number ?? null;
        $club->website = $request->website ?? "";
        $club->founded_year = $request->founded_year ?? Carbon::now();
        $club->manager_id = $request->manager_id;
        $club->save();

        $id = $club->id;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            if (Storage::exists($club->logo)) {
                Storage::delete($club->logo);
            }
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $slugName = Str::slug($club->name);
            $logo->storeAs("public/club-logos/$id-$slugName/", $logoName);

            $club->logo = "club-logos/$id-$slugName/" . $logoName;
            $club->save();
        }

        return response()->json(['message' => 'Kulüp başarıyla oluşturuldu.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param AdminUpdateClubRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function updateClub(AdminUpdateClubRequest $request, $id): JsonResponse
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            if (Storage::exists($club->logo)) {
                Storage::delete($club->logo);
            }
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $slugName = Str::slug($club->name);
            $logo->storeAs("public/club-logos/$id-$slugName/", $logoName);

            $club->logo = "club-logos/$id-$slugName/" . $logoName;
        }

        $club->name = $request->name;
        $club->email = $request->email;
        $club->city_id = $request->city_id;
        $club->manager_id = $request->manager_id;

        $club->title = $request->title ?? $club->title;
        $club->description = $request->description ?? $club->description;
        $club->phone_number = $request->phone_number ?? $club->phone_number;
        $club->website = $request->website ?? $club->website;
        $club->address = $request->address ?? $club->address;
        $club->founded_year = $request->founded_year ?? $club->founded_year;
        $club->save();
        return response()->json(['message' => 'Kulüp başarıyla güncellendi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function deleteClub($id): JsonResponse
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($club->trashed()) {
            return response()->json(['error' => 'Kulüp zaten silinmiş.'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }

        $club->delete();
        return response()->json(['message' => 'Kulüp başarıyla silindi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function restoreClub($id): JsonResponse
    {
        $club = Club::withTrashed()->find($id);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        if (!$club->trashed()) {
            return response()->json(['error' => 'Kulüp zaten aktif.'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }
        $club->restore();
        return response()->json(['message' => 'Kulüp başarıyla geri yüklendi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get users of the club.
     * @param int $clubId
     * @return JsonResponse
     */
    public function clubUsers(int $clubId): JsonResponse
    {
        $users = Club::find($clubId)->users()->paginate($this->getPerPage());
        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get events of the club.
     * @param int $clubId
     * @return JsonResponse
     */
    public function clubEvents(int $clubId): JsonResponse
    {
        $events = Club::find($clubId)->events()->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deletedClubs(): JsonResponse
    {
        $clubs = Club::onlyTrashed()->paginate($this->getPerPage());
        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deletedClub($id): JsonResponse
    {
        $club = Club::onlyTrashed()->find($id);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $club->makeVisible($this->clubHiddens);
        return response()->json($club, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
