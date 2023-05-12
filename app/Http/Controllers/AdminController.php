<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Update user role.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateRole(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role' => Rule::in(['club_manager', 'admin', 'user'])
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Geçersiz rol'], 400);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Kullanıcının rolü başarıyla güncellendi.'], 200);
    }

    /**
     * Delete user.
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUser($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            return response()->json(['error' => 'Kullanıcı zaten silinmiş.'], 400);
        }
        $user->delete();
        return response()->json(['message' => 'Kullanıcı başarıyla silindi.'], 200);
    }

    /**
     * Restore user.
     * @param int $id
     * @return JsonResponse
     */
    public function restoreUser($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404);
        }

        if ($user->deleted_at === null) {
            return response()->json(['error' => 'Kullanıcı zaten aktif'], 400);
        }

        $user->restore();
        return response()->json(['message' => 'Kullanıcı başarıyla geri yüklendi'], 200);
    }

    /**
     * Get all deleted users.
     * @return JsonResponse
     */
    public function deletedUsers(): JsonResponse
    {
        $users = User::onlyTrashed()->paginate(6);
        return response()->json($users, 200);
    }
}
