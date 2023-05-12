<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle() : \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('google')->redirect();  // google'a yönlendir
    }


    public function handleGoogleCallback() : \Illuminate\Http\RedirectResponse
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


    // bu metodu kullanmak icin once veritabaninda google_id alani gerekli
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
