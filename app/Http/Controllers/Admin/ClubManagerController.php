<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class ClubManagerController extends Controller
{
    
    protected $userHiddens = ['email', 'email_verified_at', 'created_at', 'updated_at', 'role_id', 'deleted_at', 'phone_number', 'address', 'city_id', 'google_id', 'github_id', 'pivot', 'role_id'];

    public function clubManagers()
    {
        $role_id = Role::where('slug', 'club_manager')->first()->id;
        $clubManagers = User::where('role_id', $role_id)->paginate(10);

        $clubManagers->makeVisible($this->userHiddens);
        return response()->json($clubManagers, 200);
    }

    public function clubManager($id)
    {
        $role_id = Role::where('slug', 'club_manager')->first()->id;
        $clubManager = User::where('role_id', $role_id)->find($id);

        if (!$clubManager) {
            return response()->json(['error' => 'Kulüp yöneticisi bulunamadı.'], 404);
        }

        $clubManager->makeVisible($this->userHiddens);

        return response()->json($clubManager, 200);
    }
}
