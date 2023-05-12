<?php

namespace App\Http\Controllers;
use \Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Club;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\View\View;

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
}
