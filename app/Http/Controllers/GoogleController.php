<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
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
    public function redirectToGoogle() : RedirectResponse
    {
        return Socialite::driver('google')->redirect();  // google'a yönlendir
    }
    
    /**
     * Obtain the user information from Google.
     * @return RedirectResponse
     */
    public function handleGoogleCallback() : RedirectResponse
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->to('/login');
        }

        dd($user);
        $authUser = $this->findOrCreateUser($user);

        Auth::login($authUser, true);

        return redirect()->to('/home');
    }


    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param $googleUser
     * @return User
     */
    private function findOrCreateUser($googleUser) : User
    {
        $authUser = User::where('google_id', $googleUser->id)->first();

        if ($authUser) {
            return $authUser;
        }

        return User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'password' => bcrypt('123456'),  // simdilik sabit bir şifre atadım
        ]);
    }
}
