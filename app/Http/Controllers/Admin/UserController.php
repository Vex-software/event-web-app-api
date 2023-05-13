<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;


class UserController extends Controller
{
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
