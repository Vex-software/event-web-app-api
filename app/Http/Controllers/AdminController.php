<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{

    public function updateRole(Request $request, $id)
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

    public function deleteUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            return response()->json(['error' => 'Kullanıcı zaten silinmiş.'], 400);
        }
        $user->delete();
        return response()->json(['message' => 'Kullanıcı başarıyla silindi.'], 200);
    }


    public function restoreUser($id)
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

    public function deletedUsers()
    {
        $users = User::onlyTrashed()->paginate(6);
        return response()->json($users, 200);
    }
}
