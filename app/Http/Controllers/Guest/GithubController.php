<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class GithubController extends Controller
{
    public function redirectToGithub()
    {
        $query = http_build_query([
            'client_id' => env('GITHUB_CLIENT_ID'),
            'redirect_uri' => env('GITHUB_CALLBACK_URL'),
            'scope' => 'user',
            'response_type' => 'code',
        ]);
    
        return redirect("https://github.com/login/oauth/authorize?$query");
    }

    public function handleGithubCallback()
    {
        $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'code' => request('code'),
            'redirect_uri' => env('GITHUB_CALLBACK_URL'),
        ]);

        $accessToken = explode('=', $response->body())[1];
        $accessToken = explode('&', $accessToken)[0];
       

        $user = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Accept' => 'application/json',
        ])->get('https://api.github.com/user')->json();

        dd($user);
      
    }

    public function findOrCreateUser($githubUser)
    {
        $authUser = User::where('github_id', $githubUser->id)->first();

        if ($authUser) {
            return $authUser;
        }

        return User::create([
            'name' => $githubUser->name,
            'email' => $githubUser->email,
            'github_id' => $githubUser->id,
            'password' => encrypt('my-google')
        ]);
    }
}
