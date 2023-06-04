<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Requests\Admin\User\UpdateRoleRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(SearchRequest $request): JsonResponse
    {
        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy', 'created_at');
        $query = User::orderBy($orderBy, $order);

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%');
            });
        }

        $users = $query->paginate($this->getPerPage());
        $users->getCollection()->transform(function ($user) {
            return (new UserResource($user))->toArray(request());
        });

        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show a specific user
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'Kullanıcı Bulunamadı']);
        }
        $user->load('clubs');

        return response()->json(new UserResource($user), JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function createUser(CreateRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->email = $request->email;

        $user->phone_number = $request->phone_number ?? null;
        $user->address = $request->address ?? null;
        $user->city_id = $request->city_id ?? null;
        $user->phone_number = $request->phone_number ?? null;
        $user->trust_score = $request->trust_score ?? null;
        $user->role_id = $request->role_id ?? Role::where('slug', 'user')->first()->id;
        $user->password = bcrypt($request->password);
        $user->save();

        $id = $user->id;
        if ($request->hasFile('profile_photo_path')) {
            $photo = $request->file('profile_photo_path');
            if (Storage::exists($user->profile_photo_path)) {
                Storage::delete($user->profile_photo_path);
            }
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $slugName = Str::slug($user->name);
            $photo->storeAs("public/user-photos/$id-$slugName/", $photo_name);

            $user->profile_photo_path = "user/photos/$id-$slugName/".$photo_name;
            $user->save();
        }

        return response()->json($user, JsonResponse::HTTP_CREATED, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateUser(UpdateRequest $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->email = $request->email;

        $user->phone_number = $request->phone_number ?? null;
        $user->address = $request->address ?? null;
        $user->city_id = $request->city_id ?? null;
        $user->phone_number = $request->phone_number ?? null;
        $user->trust_score = $request->trust_score ?? null;
        $user->role_id = $request->role_id ?? Role::where('slug', 'user')->first()->id;
        $user->password = bcrypt($request->password);
        $user->save();

        $id = $user->id;
        if ($request->hasFile('profile_photo_path')) {
            $photo = $request->file('profile_photo_path');
            if (Storage::exists($user->profile_photo_path)) {
                Storage::delete($user->profile_photo_path);
            }
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $slugName = Str::slug($user->name);
            $photo->storeAs("public/user-photos/$id-$slugName/", $photo_name);

            $user->profile_photo_path = "user/photos/$id-$slugName/".$photo_name;
            $user->save();
        }

        return response()->json($user, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update user role.
     *
     * @param  int  $id
     */
    public function updateRole(UpdateRoleRequest $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $user->role_id = $request->role_id;
        $user->save();

        return response()->json(['message' => 'Kullanıcının rolü başarıyla güncellendi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete user.
     *
     * @param  int  $id
     */
    public function deleteUser($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı.'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->trashed()) {
            return response()->json(['error' => 'Kullanıcı zaten silinmiş.'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }
        $user->delete();

        return response()->json(['message' => 'Kullanıcı başarıyla silindi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Restore user.
     *
     * @param  int  $id
     */
    public function restoreUser($id): JsonResponse
    {
        $user = User::withTrashed()->find($id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->trashed()) {
            $user->restore();

            return response()->json(['message' => 'Kullanıcı başarıyla geri yüklendi'], JsonResponse::HTTP_OK);
        } else {
            return response()->json(['error' => 'Kullanıcı zaten aktif'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get all deleted users.
     */
    public function deletedUsers(): JsonResponse
    {
        $users = User::onlyTrashed()->paginate($this->getPerPage());

        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get spesific deleted user.
     *
     * @param  int  $id
     */
    public function deletedUser($id): JsonResponse
    {
        $deletedUser = User::onlyTrashed()->find($id);
        if (! $deletedUser) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($deletedUser, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
