<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Club;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
            'password.confirmed' => 'Şifreler eşleşmiyor'
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:55',
            'surname' => 'required|max:65',
            'phone_number' => 'required|max:15',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData);

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
}
