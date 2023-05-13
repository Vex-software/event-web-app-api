<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Models\Role;

class UserController extends Controller
{
    /**
     * Get all users with search
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::paginate(6);
        return $users;
    }

    /**
     * Show a specific user
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $userClubs = $user->clubs()->paginate(6);
        return response()->json([
            'user' => $user,
            'user-clubs' => $userClubs
        ], 200);
    }

    /**
     * Get user's clubs
     * @param int $userId
     * @return JsonResponse
     */
    public function userClubs(int $userId): JsonResponse
    {
        $clubs = User::find($userId)->clubs()->paginate(6);
        return response()->json($clubs, 200);
    }

    /**
     * Join a club
     * @param int $clubId
     * @return JsonResponse
     */
    public function joinClub(int $clubId): JsonResponse
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kulüp yok ise 404 döndür
        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kullanıcı zaten kulüpte ise 200 döndür
        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten kulüpte'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->attach($user->id);
            return response()->json(['success' => 'Kullanıcı Kulübe katıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Leave a club
     * @param int $clubId
     * @return JsonResponse
     */
    public function leaveClub(int $clubId): JsonResponse
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $clubUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu kulüpte değil'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $club->users()->detach($user->id);
            return response()->json(['success' => 'Kullanıcı Kulüpten ayrıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Join an event
     * @param int $eventId
     * @return JsonResponse
     */
    public function joinEvent(int $eventId): JsonResponse
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Etkinlik yok ise 404 döndür
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Kullanıcı zaten etkinlikte ise 200 döndür
        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten etkinlikte'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->attach($user->id);
            return response()->json(['success' => 'Kullanıcı Etkinliğe katıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Leave an event
     * @param int $eventId
     * @return JsonResponse
     */
    public function leaveEvent(int $eventId): JsonResponse
    {
        // Kullanıcıyı doğrula
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $eventUsers)) {
            return response()->json(['error' => 'Kullanıcı zaten bu etkinlikte değil'], 409, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $event->users()->detach($user->id);
            return response()->json(['success' => 'Kullanıcı Etkinlikten ayrıldı'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update user password
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400, [], JSON_UNESCAPED_UNICODE);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Eski şifrenizi yanlış girdiniz'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json(['success' => 'Şifre güncellendi'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update user profile
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);

        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $messages = [
            'name.required' => 'İsim zorunludur',
            'surname.required' => 'Soyisim zorunludur',
            'phone_number.required' => 'Telefon numarası zorunludur',
            'email.required' => 'E-posta adresi zorunludur',
            'email.email' => 'Geçersiz e-posta adresi',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı',
            'phone_number.regex' => 'Geçersiz telefon numarası',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:55',
            'surname' => 'required|max:65',
            'phone_number' => ['required', 'regex:/^\+?\d{12}$/'],
            'email' => 'email|required|unique:users,email,' . $user->id,
            'address' => 'nullable',
            'city' => 'nullable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = preg_replace('/[^0-9]/', '', $request->input('phone_number'));

        $length = strlen($phoneNumber);
        if ($length == 10) { // Uzunluğu 10 ise başına +90 ekle
            $phoneNumber = '+90' . $phoneNumber;
        } elseif ($length == 11) { // Uzunluğu 11 ise başındaki 0'ı kaldırın ve başına +90 ekle
            $phoneNumber = '+90' . substr($phoneNumber, 1);
        }

        $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1-$2-$3-$4-$5', $phoneNumber);

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->phone_number = $phoneNumber;
        $user->email = $request->input('email');
        if (null != ($request->input('address'))) $user->address = $request->input('address'); // adress nullable kontrolü
        if (null != ($request->input('city'))) $user->city = $request->input('city'); // city nullable kontrolü
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!empty($file)) {
                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('users/photos', $filename);
                if (!empty($user->profile_photo_path)) {
                    Storage::delete($user->profile_photo_path);
                }
                $user->profile_photo_path = $filename;
            }
        }
        $user->save();

        return response()->json(['success' => 'Profil güncellendi'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete authenticated user profile photo
     * @return JsonResponse
     */
    public function deletePhoto(): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if (!empty($user->profile_photo_path)) {
            // Profil fotoğrafını sil
            Storage::delete($user->profile_photo_path);

            // Eski profil fotoğrafı adını tutan veri tabanı alanını null yap
            $user->profile_photo_path = null;
            $user->save();

            return response()->json(['success' => 'Profil fotoğrafı silindi'], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['error' => 'Profil fotoğrafı bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get joined events of user
     * @param $userId
     * @return JsonResponse
     */
    public function userEvents($userId): JsonResponse
    {
        $events = User::find($userId)->events()->paginate(6);
        return response()->json($events, 200);
    }

    /**
     * Get Authenticated user joined clubs
     * @return JsonResponse
     */
    public function myClubs(): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        $clubs = $user->clubs()->paginate(6);

        return response()->json($clubs, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get Authenticated user joined events
     * @return JsonResponse
     */
    public function myEvents(): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        $events = $user->events()->paginate(6);

        return response()->json($events, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get Authenticated user profile photo
     * @return JsonResponse
     */
    public function myPhoto(): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        $photo = $user->profile_photo_path;
        $path = storage_path('app/' . $photo);

        if (!File::exists($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }

    /**
     * Get Authenticated user profile
     * @return JsonResponse
     */
    public function whoAmI(): JsonResponse
    {
        $user = auth()->user();
        return response()->json(['user' => $user], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get user profile photo by id
     * @param int $id
     * @return Response
     */
    public function userPhoto(int $id): Response
    {
        $user = User::find($id);

        $photo = $user->profile_photo_path;
        $path = storage_path('app/' . $photo);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }
}
