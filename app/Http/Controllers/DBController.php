<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;


class DBController extends Controller
{
    public function clubs()
    {
        $clubs = Club::all();

        return view('clubs.index', compact('clubs'));
    }

    public function users()
    {
        $users = User::all();


        return view('users.index', compact('users'));
    }
}
