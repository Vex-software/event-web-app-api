<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ClubManagerController extends Controller
{
    public function index(): JsonResponse
    {
        $CMrole = Role::where('slug', 'club_manager')->first();
        $clubManagers = $CMrole->users()->paginate($this->getPerPage());

        $clubManagers->makeVisible($this->userHiddens);

        return response()->json($clubManagers, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function show($id): JsonResponse
    {
        $role_id = Role::where('slug', 'club_manager')->first()->id;
        $clubManager = User::where('role_id', $role_id)->find($id);

        if (! $clubManager) {
            return response()->json(['error' => 'Kulüp yöneticisi bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $clubManager->makeVisible($this->userHiddens);

        return response()->json($clubManager, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
