<?php

namespace App\Http\Controllers\Guest;

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use \Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     * @return RedirectResponse
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();  // google'a yönlendir
    }

    /**
     * Obtain the user information from Google.
     * @return RedirectResponse
     */
    // public function handleGoogleCallback() : RedirectResponse
    // {
    //     try {
    //         $user = Socialite::driver('google')->user();
    //     } catch (\Exception $e) {
    //         return redirect()->to('/login');
    //     }

    //     dd($user);
    //     $authUser = $this->findOrCreateUser($user);

    //     Auth::login($authUser, true);

    //     return redirect()->to('/home');
    // }


    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->to('/login');
        }

        // dd($user);
        // Find or create the user in your app's database
        $authUser = $this->findOrCreateUser($user);

        // Generate a personal access token using Laravel Passport
        $passportToken = $authUser->createToken('authToken')->accessToken;

        return redirect(env('FRONT_APP_URL'))->withCookie(
            cookie('jwt', $passportToken, 60 * 24, null, null, false, true)
        );
        

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
