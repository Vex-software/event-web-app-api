<?php

namespace App\Http\Controllers\Guest;

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find or create the user in your app's database
        $authUser = $this->findOrCreateUser($user);

        // Generate a personal access token using Laravel Passport
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
            // Collect the current user attributes

            $attributes = [
                'name' => $authUser->name,
                'surname' => $authUser->surname,
                'profile_photo_path' => $authUser->profile_photo_path,
                'email' => $authUser->email,
                'google_id' => $authUser->google_id,
                'password' => $authUser->password,
            ];

            // Merge the existing attributes with any missing ones from the Google user
            $newAttributes = array_merge($attributes, array_filter([
                'name' => $googleUser->user['given_name'],
                'surname' => $googleUser->user['family_name'],
                'profile_photo_path' => $googleUser->user['picture'],
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]));

            // Update the user with the merged attributes
            $authUser->update($newAttributes);
            $authUser->save();

            return $authUser;
        }

        // If no user found, create a new user with the Google user's details
        return User::create([
            'name' => $googleUser->user['given_name'],
            'surname' => $googleUser->user['family_name'],
            'profile_photo_path' => $googleUser->user['picture'],
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'password' => bcrypt('123456'),  // simdilik sabit bir şifre atadım
        ]);
    }
}
