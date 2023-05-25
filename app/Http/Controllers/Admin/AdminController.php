<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Role;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        $users = User::paginate($this->getPerPage());
        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function admins(): JsonResponse
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admins = $adminRole->users()->paginate($this->getPerPage());
        return response()->json($admins, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
