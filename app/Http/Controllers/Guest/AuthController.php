<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Http\Requests\Guest\EmailVerificationRequest;
use App\Http\Requests\Guest\LoginRequest;
use App\Http\Requests\Guest\LostPasswordRequest;
use App\Http\Requests\Guest\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

class AuthController extends Controller
{
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $otp = $request->input('otp');
        $id = $request->input('id');

        $user = User::where('id', $id)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Email doğrulama başarısız..',
            ], 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        if ($user->email_verified_at != null) {
            return response()->json([
                'message' => 'Email zaten doğrulanmış.',
            ], 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        if ($user->otp == $otp) {

            if ($user->last_activity_at != null) {
                $lastActivity = Carbon::parse($user->last_activity_at);
                $now = Carbon::now();
                $diff = $lastActivity->diffInMinutes($now);
                if ($diff > parent::getOtpExpiresInMinutes()) {
                    return response()->json([
                        'message' => 'Kod\'un süresi dolmuş. Lütfen tekrar kayıt olunuz.',
                    ], 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }
            }

            $user->email_verified_at = Carbon::now();
            $user->email = $user->unauthorized_email;
            $user->otp = null;
            $user->unauthorized_email = null;
            $user->status_id = Status::getActiveStatus()->id;
            $user->save();

            DB::table('users')
                ->where('unauthorized_email', $user->email)->delete();

            return response()->json([
                'message' => 'Email başarıyla doğrulandı. Giriş yapabilirsiniz.',
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'message' => 'Email doğrulama başarısız..',
            ], 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    }

    /**
     * Login user and create token
     *
     * @param  Request  $request
     * @return JsonResponse|void
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $email = $request->input('email');

        if (User::where('email', $email)->first()) {
            return response()->json([
                'message' => 'Bu email adresi ile daha önce kayıt olunmuş.',
            ], JsonResponse::HTTP_CONFLICT, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->phone_number = $request->input('phone_number') ?? null;
        $user->unauthorized_email = $request->input('email');
        $user->trust_score = 0;

        $uemail = 'unauthorized/'.time().'/'.$email;
        $user->email = $uemail;

        $user->password = bcrypt(random_bytes(20));
        $user->address = $request->input('address') ?? null;
        $user->city_id = $request->input('city_id') ?? null;
        $user->password = bcrypt($request->input('password'));
        $user->email_verified_at = null;

        $user->last_activity_at = Carbon::now();
        $user->status_id = Status::getDraftStatus()->id;
        $user->save();

        $user = User::where('id', $user->id)->first();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            if (Storage::exists($user->profile_photo_path)) {
                Storage::delete($user->profile_photo_path);
            }
            $photoName = time().'.'.$photo->getClientOriginalExtension();
            $slugName = Str::slug($user->name);

            $photo->storeAs("public/user_profile_photo/$user->id-$slugName/", $photoName);

            $user->profile_photo_path = "user_profile_photo/$user->id-$slugName/".$photoName;
            $user->save();
        }

        $mailController = new MailController();
        $mail = $mailController->sendVerificationEmail($user);

        if ($mail) {
            return response()->json([
                'message' => 'Kayıt başarılı. Email doğrulama için mailinizi kontrol ediniz.',
                'user' => new UserResource($user),
                'callback_url' => route('verify-email'),
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'message' => 'Mail gönderilemedi. Lütfen daha sonra tekrar deneyiniz.',
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    }

    /**
     * Login user and create token
     *
     * @param  Request  $request
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {

            $user = User::where('unauthorized_email', $credentials['email'])->first();
            if ($user) {
                return response()->json([
                    'message' => 'Email adresiniz doğrulanmamış. Lütfen email adresinizi doğrulayınız.',
                ], JsonResponse::HTTP_UNAUTHORIZED, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'message' => 'Kullanıcı bulunamadı. Lütfen bilgilerinizi kontrol ediniz.',
                ], JsonResponse::HTTP_UNAUTHORIZED, [], JSON_UNESCAPED_UNICODE);
            }
        }

        if ($user->status_id == Status::getEmailNotVerifiedStatus()->id) {
            return response()->json([
                'message' => 'Kullanının email adresi doğrulanmamış. Lütfen email adresinizi doğrulayınız.',
            ], JsonResponse::HTTP_UNAUTHORIZED, [], JSON_UNESCAPED_UNICODE);
        }

        if (! Auth::attempt($credentials)) {
            return response()->json(
                [
                    'message' => 'Kullanıcı adı veya şifre hatalı',
                    'errors' => [
                        'email' => 'Kullanıcı adı veya şifre hatalı',
                    ],
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $user = $request->user();
        $dbUser = User::find($user->id);

        if (! $dbUser) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı. Lütfen bilgilerinizi kontrol ediniz.',
            ], JsonResponse::HTTP_UNAUTHORIZED, [], JSON_UNESCAPED_UNICODE);
        }

        $accessToken = $dbUser->access_token;

        if (! $accessToken || Passport::token()->where('user_id', $user->id)->where('revoked', false)->count() === 0) {
            // Token yok veya süresi dolmuş, yeni token oluştur
            $accessToken = $user->createToken('authToken')->accessToken;
            $dbUser->access_token = $accessToken;
            $dbUser->access_token_expires_at = Carbon::now()->addDays(parent::$tokenExpiresInDays);
            $dbUser->save();
        } elseif (Carbon::parse($dbUser->access_token_expires_at)->isPast()) {
            // Token süresi dolmuş, yeni token oluştur
            Passport::token()->where('user_id', $user->id)->where('revoked', false)->update(['revoked' => true]);
            $accessToken = $user->createToken('authToken')->accessToken;
            $dbUser->access_token = $accessToken;
            $dbUser->access_token_expires_at = Carbon::now()->addDays(30);
            $dbUser->save();
        }

        $dbUser->last_login_at = now();
        $dbUser->last_activity_at = now();
        $dbUser->save();

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        $user = User::find($request->user()->id);
        $user->access_token = null;
        $user->access_token_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Çıkış yapıldı',
        ], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Send Email for reset password
     */
    public function lostPassword(LostPasswordRequest $request): JsonResponse
    {
        $input = $request->only('email');
        $email = $input['email'];

        $existingToken = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if ($existingToken) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->update([
                    'token' => $token,
                    'created_at' => now(),
                ]);
        } else {
            $token = Str::random(60);

            DB::table('password_reset_tokens')
                ->insert([
                    'email' => $email,
                    'token' => $token,
                    'created_at' => now(),
                ]);
        }

        $user = User::where('email', $email)->first();

        $mailController = new MailController();
        $mail = $mailController->sendResetPasswordEmail($user, $token);

        return response()->json(
            [
                'message' => 'Şifre sıfırlama bağlantısı gönderildi',
                'callback_url' => route('password.reset'),
            ]
        );
    }

    public function resetPassword(Request $request)
    {
        $input = $request->only('email', 'token', 'password', 'password_confirmation');
        $validator = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $response = Password::reset($input, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });
        $message = $response == Password::PASSWORD_RESET ? 'Password reset successfully' : ' Password not reset ';

        return response()->json($message);
    }
}
