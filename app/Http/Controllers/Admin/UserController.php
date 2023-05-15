<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    protected $userHiddens = ['email', 'email_verified_at', 'created_at', 'updated_at', 'role_id', 'deleted_at', 'phone_number', 'address', 'city_id', 'google_id', 'github_id', 'pivot', 'role_id'];

    public function users()
    {
        $users = User::paginate(10);
        $users->makeVisible($this->userHiddens);
        return response()->json($users, 200);
    }


    public function user($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı.'], 404);
        }
        $user->makeVisible($this->userHiddens);
        return response()->json($user, 200);
    }

    public function createUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users',
            'phone_number' => 'nullable|unique:users',
            'address' => 'nullable',
            'city_id' => 'nullable|integer|exists:cities,id',
            'description' => 'nullable',
            'profile_photo' => 'nullable',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email ?? null;
        $user->phone_number = $request->phone_number ?? null;
        $user->address = $request->address ?? null;
        $user->city_id = $request->city_id ?? null;
        $user->description = $request->description ?? null;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->hasFile('profile_photo')) {
            $user->profile_photo = Storage::putFile('public/profile_photos', $request->file('profile_photo'));
            $user->save();
        }

        $user->makeVisible($this->userHiddens);

        return response()->json($user, 201);
    }


    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:users,name,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users,phone_number,' . $user->id],
            'address' => ['required', 'string', 'max:255', 'unique:users,address,' . $user->id],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'description' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user->name = $request->name;

        if ($request->hasFile('profile_photo')) {
            $profile_photo = $request->file('profile_photo');
            Storage::delete($user->profile_photo);
            $profile_photoName = time() . '.' . $profile_photo->getClientOriginalExtension();
            $profile_photo->storeAs('public/profile_photos', $profile_photoName);
            $user->profile_photo = $profile_photoName;
        }

        $user->email = $request->email;

        $user->phone_number = $request->phone_number;

        $user->address = $request->address;

        $user->city_id = $request->city_id;

        $user->description = $request->description;

        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }
        if ($request->role != null) {
            $user->role = $request->role;
        }

        if ($request->club_id != null) {
            $user->club_id = $request->club_id;
        }

        if ($request->profile_photo_path != null) {
            $user->profile_photo_path = $request->profile_photo_path;
        }
        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo_path')) {
            $profile_photo_path = $request->file('profile_photo_path');
            Storage::delete($user->profile_photo_path);
            $profile_photo_pathName = time() . '.' . $profile_photo_path->getClientOriginalExtension();
            $profile_photo_path->storeAs('public/profile_photos', $profile_photo_pathName);
            $user->profile_photo_path = $profile_photo_pathName;
        }
        $user->save();
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

        $validator = Validator::make($request->all(), [
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Geçersiz rol'], 400);
        }

        $user->role_id = $request->role_id;
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
        $users->makeVisible($this->userHiddens); // makeVisible() methodu ile gizli alanları görünür hale getirdim. Editör hata verebilir
        return response()->json($users, 200);
    }

    /**
     * Get spesific deleted user.
     * @param int $id
     * @return JsonResponse
     */
    public function deletedUser($id): JsonResponse
    {
        $deletedUser = User::onlyTrashed()->find($id);
        if (!$deletedUser) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404);
        }
        $deletedUser->makeVisible($this->userHiddens);
        return response()->json($deletedUser, 200);
    }
}
