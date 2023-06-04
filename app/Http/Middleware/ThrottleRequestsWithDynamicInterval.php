<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleRequestsWithDynamicInterval extends ThrottleRequests
{
    protected function resolveRequestsWithinInterval($request)
    {
        $interval = config('app.request_throttle_interval', 6); // Varsayılan olarak 6 saniye

        return $interval;
    }
}
