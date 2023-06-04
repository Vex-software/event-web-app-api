<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendVerificationEmail($user)
    {
        $otp = $this->generateOTP();

        $user->otp = $otp;
        $user->save();

        Mail::to($user->unauthorized_email)->send(new VerifyEmail($user));

        return response()->json([
            'message' => 'Verification email sent successfully.',
        ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function generateOTP($length = 6)
    {
        $otp = '';
        $characters = '0123456789';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[rand(0, $charactersLength - 1)];
        }

        return $otp;
    }

    public function sendResetPasswordEmail($user, $token)
    {
        $user = User::where('email', $user->email)->first();
        if ($user) {
            Mail::to($user->email)->send(new ResetPassword($user, $token));

            return response()->json([
                'message' => 'Reset password email sent successfully.',
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            // Log::info('Var Olmayan Mail Adresine Sıfırlama İsteği Geldi : ' . $email . ' ' . now() . ' ' . $request->ip() . ' ' . $request->header('User-Agent') . ' ' . $request->header('X-Forwarded-For'));
        }
    }
}
