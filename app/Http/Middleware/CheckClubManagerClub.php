<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClubManagerClub
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->managerOfClub == null) {
            return response()->json(['error' => 'Yöneticisi olduğunuz kulüp bulunamadı. Site yöneticisi ile iletişime geçin'], 403);
        }
        return $next($request);
    }
}
