<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function users(): JsonResponse
    {
        $users = User::paginate($this->getPerPage());

        $transformedUsers = UserResource::collection($users);

        return response()->json($transformedUsers, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function admins(): JsonResponse
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admins = $adminRole->users()->paginate($this->getPerPage());

        $transformedAdmins = UserResource::collection($admins);

        return response()->json($transformedAdmins, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
