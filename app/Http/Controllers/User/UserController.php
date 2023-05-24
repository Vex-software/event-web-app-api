<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\UserUpdatePasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserResendEmailRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Http\Requests\User\UserUpdateProfileRequest;
use App\Http\Requests\User\UserVerifyEmailRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Club;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Get all users
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $users = User::paginate($this->getPerPage());
        return response()->json($users, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show a specific user
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Kullanıcı Bulunamadı']);
        }
        $user->load('clubs');
        return response()->json($user, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get user's clubs
     * @param int $userId
     * @return JsonResponse
     */
    public function userClubs(int $userId): JsonResponse
    {
        $user = User::find($userId);
        $clubs = $user->clubs()->paginate($this->getPerPage());
        return response()->json($clubs, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Join a club
     * @param int $clubId
     * @return JsonResponse
     */
    public function joinClub(int $clubId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (!$club) {
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
     * @param int $clubId
     * @return JsonResponse
     */
    public function leaveClub(int $clubId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['error' => 'Kulüp bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $clubUsers = $club->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $clubUsers)) {
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
     * @param int $eventId
     * @return JsonResponse
     */
    public function joinEvent(int $eventId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (!$event) {
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
     * @param int $eventId
     * @return JsonResponse
     */
    public function leaveEvent(int $eventId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Etkinlik bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $eventUsers = $event->users()->pluck('users.id')->toArray();
        if (!in_array($user->id, $eventUsers)) {
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
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(UserUpdatePasswordRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (!Hash::check($request->old_password, $user->password)) {
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
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(UserUpdateProfileRequest $request): JsonResponse
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
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

        return response()->json(['success' => 'Profil güncellendi'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete authenticated user profile photo
     * @return JsonResponse
     */
    public function deletePhoto(): JsonResponse
    {
        $user = User::find(auth()->user()->id);

        if (!$user) {
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
     * @param $userId
     * @return JsonResponse
     */
    public function userEvents($userId): JsonResponse
    {
        $events = User::find($userId)->events()->paginate($this->getPerPage());
        return response()->json($events, 200);
    }

    /**
     * Get Authenticated user profile photo
     * @return JsonResponse
     */
    public function myPhoto()
    {
        $path = DB::table('users')->where('id', auth()->user()->id)->value('profile_photo_path');

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    /**
     * Get Authenticated user profile
     * Not : Kullanıcı kendi bilgilerinin tamamını görebilir
     * @return JsonResponse
     */
    public function whoAmI(): JsonResponse
    {
        $user = User::find(auth()->user()->id);

        return response()->json($user->getAllAttributes(), JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get user profile photo
     * @param $id
     * @return JsonResponse|StreamedResponse
     */
    public function userPhoto($id)
    {
        $path = DB::table('users')->where('id', $id)->value('profile_photo_path');

        if (empty($path)) {
            return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
            $type = get_headers($path, 1)["Content-Type"];
            return response()->stream(function () use ($path) {
                echo file_get_contents($path);
            }, 200, ['Content-Type' => $type]);
        } else {
            if (!File::exists($path)) {
                return response()->json(['error' => 'Fotoğraf bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
            }

            $type = File::mimeType($path);
            return response()->file($path, ['Content-Type' => $type]);
        }
    }

    /**
     * Verify email
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(UserVerifyEmailRequest $request): JsonResponse
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['error' => 'Geçersiz doğrulama bağlantısı'], JsonResponse::HTTP_BAD_REQUEST, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => 'E-posta adresi zaten doğrulanmış'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        $user->markEmailAsVerified();

        return response()->json(['success' => 'E-posta adresiniz başarıyla doğrulandı'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resend email
     * @param Request $request
     * @return JsonResponse
     */
    public function resendEmail(UserResendEmailRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Kullanıcı zaten onaylanmış.'], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_UNICODE);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Doğrulama e-postası gönderildi.'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Reset password
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(UserResetPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json(['error' => 'E-posta gönderimi başarısız oldu.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_UNESCAPED_UNICODE);
    }

    // /**
    //  * Get Authenticated user joined clubs
    //  * @return JsonResponse
    //  */
    // public function joinedClubs(): JsonResponse
    // {
    //     $user = User::find(auth()->user()->id);
    //     $clubs = $user->clubs()->with('manager')->paginate(6);

    //     return response()->json($clubs, 200, [], JSON_UNESCAPED_UNICODE);
    // }

    // /**
    //  * Get Authenticated user joined events
    //  * @return JsonResponse
    //  */
    // public function joinedEvents(): JsonResponse
    // {
    //     $user = User::find(auth()->user()->id);
    //     $events = $user->events()->with('eventCategory')->with('club')->paginate(6);

    //     // $events->getCollection()->transform(function ($event) {
    //     //     return [
    //     //         'id' => $event->id,
    //     //         'name' => $event->name,
    //     //         'category' => $event->eventCategory->name,
    //     //         'club' => $event->club->name,
    //     //         'start_date' => $event->start_date,
    //     //         'end_date' => $event->end_date,
    //     //         'created_at' => $event->created_at,
    //     //         'updated_at' => $event->updated_at,
    //     //         'deleted_at' => $event->deleted_at,
    //     //     ];
    //     // });


    //     return response()->json($events, 200, [], JSON_UNESCAPED_UNICODE);
    // }
}
