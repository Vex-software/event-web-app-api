<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Update user role.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateRole(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [  ///  bu sekilde olmamali.
            'role' => Rule::in(['club_manager', 'admin', 'user'])
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Geçersiz rol'], 400);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Kullanıcının rolü başarıyla güncellendi.'], 200);
    }
}
