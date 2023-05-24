<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserLostPasswordRequest;
use App\Http\Requests\User\UserRegisterRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class LoginController extends Controller
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        /* Fotoğrafın kaydedilmesi */
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!empty($file)) {
                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('users/photos', $filename);
            }
        } else {
            $path = 'users/photos/default.png';
        }


        /* Telefon numarası formatlanması */
        $phoneNumber = $request->input('phone_number');
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) == 10) {
            $phoneNumber = '+90' . $phoneNumber;
        } elseif (strlen($phoneNumber) == 11) {
            $phoneNumber = '+90' . substr($phoneNumber, 1);
        }

        $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1-$2-$3-$4-$5', $phoneNumber);

        $user = new User();
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->phone_number = $phoneNumber;
        $user->email = $request->input('email');
        if (null != ($request->input('profile_photo_path'))) $user->profile_photo_path = $request->input('profile_photo_path'); // adress nullable kontrolü
        if (null != ($request->input('address'))) $user->address = $request->input('address'); // adress nullable kontrolü
        if (null != ($request->input('city'))) $user->city = $request->input('city'); // city nullable kontrolü
        if (null != $path) $user->profile_photo_path = $path;
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $user = User::where('email', $request->input('email'))->first();

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['user' => $user, 'access_token' => $accessToken], JsonResponse::HTTP_CREATED, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(
                [
                    'message' => 'Kullanıcı adı veya şifre hatalı',
                    'errors' => [
                        'email' => 'Kullanıcı adı veya şifre hatalı'
                    ]
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $user = $request->user();
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer'
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' =>
            'Çıkış yapıldı'
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Send Email for reset password
     * @param Request $request
     * @return JsonResponse
     */
    public function lostPassword(UserLostPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $user->sendPasswordResetNotification();

        return response()->json([
            'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi'
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function resendEmail(UserLostPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'errors' => [
                    'message' => 'Bu e-posta adresine sahip bir kullanıcı bulunamadı'
                ]
            ], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        $user->sendPasswordResetNotification();

        return response()->json([
            'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi'
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}
