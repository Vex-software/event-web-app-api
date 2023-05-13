<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
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
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $messages = [
            'name.required' => 'İsim zorunludur',
            'surname.required' => 'Soyisim zorunludur',
            'phone_number.required' => 'Telefon numarası zorunludur',
            'email.required' => 'E-posta adresi zorunludur',
            'email.email' => 'Geçersiz e-posta adresi',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı',
            'password.required' => 'Şifre zorunludur',
            'password.confirmed' => 'Şifre eşleşmiyor',
            'phone_number.regex' => 'Geçersiz telefon numarası',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:55',
            'surname' => 'required|max:65',
            'phone_number' => ['regex:/^\+?\d{12}$/'],
            'email' => 'email|required|unique:users',
            'address' => 'nullable',
            'city' => 'nullable',
            'password' => 'required|confirmed'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $path = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!empty($file)) {
                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('users/photos', $filename);
            }
        }

        $phoneNumber = preg_replace('/[^0-9]/', '', $request->input('phone_number'));

        $length = strlen($phoneNumber);
        if ($length == 10) { // Uzunluğu 10 ise başına +90 ekle
            $phoneNumber = '+90' . $phoneNumber;
        } elseif ($length == 11) { // Uzunluğu 11 ise başındaki 0'ı kaldırın ve başına +90 ekle
            $phoneNumber = '+90' . substr($phoneNumber, 1);
        }
        $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1-$2-$3-$4-$5', $phoneNumber);
        $phoneNumber = str_replace('++', '+', $phoneNumber);



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

        return response(['user' => $user, 'access_token' => $accessToken]);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'E-posta adresi zorunludur',
            'email.email' => 'Geçersiz e-posta adresi',
            'password.required' => 'Şifre zorunludur'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(
                [
                    'errors' =>
                    [
                        'message' => 'E-posta adresi veya şifre hatalı'
                    ]
                ],
                401
            );
        }

        $user = $request->user();

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
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
        ]);
    }

    /**
     * Send Email for reset password
     * @param Request $request
     * @return JsonResponse
     */
    public function lostPassword(Request $request): JsonResponse
    {
        $rules = [
            'email' => 'required|email'
        ];

        $messages = [
            'email.required' => 'E-posta adresi zorunludur',
            'email.email' => 'Geçersiz e-posta adresi'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail(auth()->user()->id);
        $userEmail = $user->email;

        if ($userEmail != $request->email) {
            return response()->json([
                'errors' => [
                    'message' => 'E-posta adresi hatalı' //giris yapmakta olan kullanicinin emaili degil 
                ]
            ], 422);
        }

        $user->sendPasswordResetNotification($request->email);

        return response()->json([
            'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi'
        ]);
    }

    /**
     * Resend email
     * @param Request $request
     * @return JsonResponse
     */
    public function resendEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Kullanıcı zaten onaylanmış.'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Doğrulama e-postası gönderildi.'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    
    /**
     * Reset password
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.']);
        }

        return response()->json(['error' => 'E-posta gönderimi başarısız oldu.'], 500);
    }

    /**
     * Verify email
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['error' => 'Geçersiz doğrulama bağlantısı'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => 'E-posta adresi zaten doğrulanmış'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $user->markEmailAsVerified();

        return response()->json(['success' => 'E-posta adresiniz başarıyla doğrulandı'], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
