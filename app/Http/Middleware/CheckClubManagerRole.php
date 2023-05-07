<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckClubManagerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userRole = User::findOrFail(auth()->user()->id)->role;
        if ($userRole != 'club_manager') {
            return response()->json(['error' => 'Yetkiniz yok'], 403);
        }
        return $next($request);
    }
}
