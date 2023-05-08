<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Club;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{



    public function register(Request $request)
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
            'phone_number' => ['required', 'regex:/^\+?\d{12}$/'],
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


    public function login(Request $request)
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

        // eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZjFlOGVhMjg2NDJmZjgwOTY4ZDllOTYwNGFiZTc4MjQ4ODg5Yzk4M2FkYWE2ZDA1ZWU5OGUyZmE1ZTAyMmJhZWY4ZmY5MWFkNzM2YzdhYjUiLCJpYXQiOjE2ODMzNjQ3MTEuOTY1MTQ2LCJuYmYiOjE2ODMzNjQ3MTEuOTY1MTUsImV4cCI6MTcxNDk4NzExMS45NDc4MjQsInN1YiI6IjIiLCJzY29wZXMiOltdfQ.auhQ6DZ2QTXtxr_uWNhtbunXDmZyLA1Dr0ivaLcSMrAxmFHOQ_5u41bsI0bWJUdUDBs7ahAg_mIZc-sY1GWCpPY_m2zL1QJsad4dWeDDq0LMArqsctZM5I6aQFZsBc2p8LytMPxW06yrqyq0xcA2yXe4C01RHtu8yrVbxqftGJN3XDEPwxL2jFhP5NzwvbdR5tznMQNQutje6YHeRVfwUWiPOFWYUjKWusJc1AdRG2lSHgbi9xWsRQZ-tn3-bF_exB8zUR3T7mhwUV9aY0voF4o4FF3F7wP7JAMTKcyi2VLs7sdohkY6kbgeqs2kRLasHfmo-uVOM4756yuE3wSDlkP1hfaecxg_kdfbcpeCr4GA0PTCbY_hCixUTBLxGFlBknqvnyXEWhGW_3dPPRfKF8ufyNtDaOg3RGZP-o0P01mdlLrxvaRrUDR-wpHQMO4wXPA8RTrnfJfovZOr55zx4sW9OeQyqdDmGE-zny5IRXxpVCkwak67Y8cnMGU3WDw4TCbIsgzvLKtCRfsZkmMzV373t-BnOxwguZ8zKsqMx8ECWOaW8c6rj9s6-yJaprP_cGyBHkqWnimGLfUzp-glQGJb1v7qrxtm91paU3qFVz5zwflzinVEbwFUzyVbLXD19EY1L9rFIRY7GlYpRhw0aOmqIO7OOyR_shDszT6sLao

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' =>
            'Çıkış yapıldı'
        ]);
    }


    public function lostPassword(Request $request)
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

        // $user->sendPasswordResetNotification($request->email);

        return response()->json([
            'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi'
        ]);
    }


    public function resetPassword(Request $request)
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

    public function verifyEmail(Request $request)
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

    public function resendEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Kullanıcı zaten onaylanmış.'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Doğrulama e-postası gönderildi.'], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
