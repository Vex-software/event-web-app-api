<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\JsonResponse;


class ClubManagerController extends Controller
{
    /**
     * Get All Club Managers.
     * @return JsonResponse
     */
    public function clubManagers(): JsonResponse
    {
        $clubManagerRole = Role::where('slug', 'club_manager')->first();
        return response()->json($clubManagerRole->users, 200);
    }

    
}
