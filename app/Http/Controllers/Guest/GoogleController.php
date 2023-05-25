<?php

namespace App\Http\Controllers\Guest;

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class GoogleController extends Controller
{

    /**
     * Redirect the user to the Google authentication page.
     * @return Jsonresponse
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED, [], JSON_UNESCAPED_UNICODE);
        }

        $authUser = $this->findOrCreateUser($user);
        $passportToken = $authUser->createToken('authToken')->accessToken;
        $authUser->token = $passportToken;

        return response()->json(['token' => $passportToken, 'user' => $authUser]);
    }
    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param $googleUser
     * @return User
     */
    private function findOrCreateUser($googleUser): User
    {
        $authUser = User::where('email', $googleUser->email)->first();

        if ($authUser) {
            $attributes = [
                'name' => $authUser->name,
                'surname' => $authUser->surname,
                'profile_photo_path' => $authUser->profile_photo_path,
                'email' => $authUser->email,
                'google_id' => $authUser->google_id,
                'password' => $authUser->password,
            ];

            $newAttributes = array_merge($attributes, array_filter([
                'name' => $googleUser->user['given_name'],
                'surname' => $googleUser->user['family_name'],
                'profile_photo_path' => $googleUser->user['picture'],
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]));

            $authUser->update($newAttributes);
            $authUser->save();

            return $authUser;
        }

        // Eger kullanici yoksa yeni kullanici olustur
        return User::create([
            'name' => $googleUser->user['given_name'],
            'surname' => $googleUser->user['family_name'],
            'profile_photo_path' => $googleUser->user['picture'],
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'password' => bcrypt(strval(mt_rand(10000000, 99999999))),
        ]);
    }
}
