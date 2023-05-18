<?php

namespace App\Http\Controllers;

use \Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Club;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class DBController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Club[]|Collection|Response
     */
    public function clubs(): View
    {
        $clubs = Club::all();

        return view('clubs.index', compact('clubs'));
    }

    /**
     * Display All Users.
     * @return User[]|Collection|Response|View
     */
    public function users(): View
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }




    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();  // google'a yönlendir
    }

    public function handleGoogleCallback()
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


        $authUser->token = $passportToken;


        // return redirect(env('FRONT_APP_URL'))->withCookie(
        //     cookie('jwt', $passportToken, 60 * 24, null, null, false, true)
        // );


        // Parent pencereye yönlendirme
        return $this->redirectParent($authUser);

        // return "<script>window.opener.location.href = '/success?token=$passportToken'; window.close();</script>";
        // return '<script>window.opener.postMessage(' . json_encode($user) . ', "http://127.0.0.1:8000/login"); window.close();</script>';
    }

    private function redirectParent($data)
    {
        $csrf_token = csrf_token();

        // Convert PHP object to JSON string
        $json_data = json_encode($data);


        return "<script>
                    const form = window.opener.document.createElement('form');
                    form.method = 'POST';
                    form.action = '/login';
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = '$csrf_token';
                    form.appendChild(tokenInput);
                    const dataInput = document.createElement('input');
                    dataInput.type = 'hidden';
                    dataInput.name = 'data';
                    dataInput.value = '$json_data';
                    form.appendChild(dataInput);
                    window.opener.document.body.appendChild(form);
                    form.submit();
                    window.close();
              </script>";
    }

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
