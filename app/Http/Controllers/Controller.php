<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    private $per_page = 10;

    private static $otpExpiresInMinutes = 60 * 24;

    public static $tokenExpiresInDays = 30;

    // public static function getTokenExpiresInDays()
    // {
    //     return self::$tokenExpiresInDays;
    // }
    public function getPerPage()
    {
        return $this->per_page;
    }

    public static function getOtpExpiresInMinutes()
    {
        return self::$otpExpiresInMinutes;
    }
}
