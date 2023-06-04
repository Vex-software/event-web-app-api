<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\ClubResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Get all users
     *
     * @param  Request  $request
     */
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

    /**
     * Get user's clubs
     */
    public function userClubs(int $userId, SearchRequest $request): JsonResponse
    {
        $user = User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $query = $user->clubs();

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where('name', 'like', '%'.$searchKeyword.'%');
        }

        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy');

        if (in_array($orderBy, ['name', 'created_at', 'updated_at'])) {
            $query->orderBy($orderBy, $order);
        }

        $perPage = $this->getPerPage();

        $clubs = $query->paginate($perPage);

        $clubs->getCollection()->transform(function ($club) {
            return (new ClubResource($club))->toArray(request());
        });

        // Dönüştürülmüş kulüpleri JSON formatında dönüyoruz.
        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Join a club
     */
    public function joinClub(int $clubId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (! $club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten kulüpte'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->attach($user->id);

            return response()->json(['success' => 'Kullanıcı Kulübe katıldı'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Leave a club
     */
    public function leaveClub(int $clubId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (! $club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (! in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu kulüpte değil'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->detach($user->id);

            return response()->json(['success' => 'Kullanıcı Kulüpten ayrıldı'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Join an event
     */
    public function joinEvent(int $eventId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (! $event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        // Kullanıcı zaten etkinlikte ise 200 döndür
        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten etkinlikte'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->attach($user->id);

            return response()->json(['success' => 'Kullanıcı Etkinliğe katıldı'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Leave an event
     */
    public function leaveEvent(int $eventId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (! $event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (! in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu etkinlikte değil'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->detach($user->id);

            return response()->json(['success' => 'Kullanıcı Etkinlikten ayrıldı'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update user password
     *
     * @param  Request  $request
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Eski şifrenizi yanlış girdiniz'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['success' => 'Şifre güncellendi'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update user profile
     *
     * @param  Request  $request
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $phoneNumber = preg_replace('/[^0-9]/', '', $this->input('phone_number'));

        if (Str::startsWith($phoneNumber, '90')) {
            $phoneNumber = '+90'.substr($phoneNumber, -10);
        }

        $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '$1-$2-$3-$4-$5', $phoneNumber);

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->phone_number = $phoneNumber;
        $user->email = $request->input('email');
        if (null != ($request->input('address'))) {
            $user->address = $request->input('address');
        } // adress nullable kontrolü
        if (null != ($request->input('city'))) {
            $user->city = $request->input('city');
        } // city nullable kontrolü
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (! empty($file)) {
                $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('users/photos', $filename);
                if (! empty($user->profile_photo_path)) {
                    Storage::delete($user->profile_photo_path);
                }
                $user->profile_photo_path = $filename;
            }
        }
        $user->save();

        return response()->json(['success' => 'Profil güncellendi'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete authenticated user profile photo
     */
    public function deletePhoto(): JsonResponse
    {
        $user = User::find(auth()->user()->id);

        if (! $user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->profile_photo_path) {
            if (Storage::exists($user->profile_photo_path)) {
                Storage::delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
            $user->save();

            return response()->json(['success' => 'Profil fotoğrafı silindi'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(['error' => 'Profil fotoğrafı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Get joined events of user
     */
    public function userEvents(int $userId, SearchRequest $request): JsonResponse
    {
        $user = User::findOrFail($userId);

        $query = $user->events();

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('title', 'like', '%'.$searchKeyword.'%')
                    ->orWhere('description', 'like', '%'.$searchKeyword.'%');
            });
        }

        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy');

        if (in_array($orderBy, ['name', 'start_time', 'end_time'])) {
            $query->orderBy($orderBy, $order);
        }

        $perPage = $this->getPerPage();
        $events = $query->paginate($perPage);

        $events->getCollection()->transform(function ($event) {
            return new EventResource($event);
        });

        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function myPhoto()
    {
        $path = DB::table('users')->where('id', auth()->user()->id)->value('profile_photo_path');

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return response()->json(['photo_url' => $path]);
        } else {
            if (! File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);

            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    public function whoAmI(): JsonResponse
    {
        $user = auth()->user();

        $transformedUser = new UserResource($user);

        return response()->json($transformedUser, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function userPhoto($id)
    {
        $path = DB::table('users')->where('id', $id)->value('profile_photo_path');

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            dd($path);
            $type = get_headers($path, 1)['Content-Type'];

            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (! Storage::exists('public/'.$path)) {
                dd($path);

                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = Storage::mimeType('public/'.$path);

            return response()->file(Storage::path('public/'.$path), ['Content-Type' => $type]);
        }
    }

    public function joinedClubs(SearchRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy', 'created_at');
        $paginate = $request->input('paginate', $this->getPerPage());

        $clubs = $user->clubs()->orderBy($orderBy, $order);

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $clubs->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%');
            });
        }

        $clubs = $clubs->paginate($paginate);
        $clubs->getCollection()->transform(function ($club) {
            return (new ClubResource($club))->toArray(request());
        });

        return response()->json($clubs, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get Authenticated user joined events
     */
    public function joinedEvents(SearchRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy', 'created_at');
        $paginate = $request->input('paginate', $this->getPerPage());

        $events = $user->events()->orderBy($orderBy, $order);

        if ($request->has('q')) {
            $searchKeyword = $request->input('q');
            $events->where(function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%'.$searchKeyword.'%');
            });
        }

        $events = $events->paginate($paginate);
        $events->getCollection()->transform(function ($club) {
            return (new ClubResource($club))->toArray(request());
        });

        return response()->json($events, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
