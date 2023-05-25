<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $per_page = 10;

    protected $userHiddens = [
        'email',
        'email_verified_at',
        'created_at',
        'updated_at',
        'role_id',
        'deleted_at',
        'phone_number',
        'address',
        'city_id',
        'google_id',
        'github_id',
        'pivot',
        'role_id'
    ];


    protected $eventHiddens = [
        'created_at',
        'updated_at',
        'deleted_at',
        'pivot',
    ];

    protected $clubHiddens = [
        'created_at',
        'updated_at',
        'deleted_at',
        'email',
        'phone_number',
        'pivot',
    ];

    public function getPerPage()
    {
        return $this->per_page;
    }
}
