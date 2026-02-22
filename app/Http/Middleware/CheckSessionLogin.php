<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSessionLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        // User sudah login
        if (session()->has('user_id') || Auth::check()) {
            return $next($request);
        }

        // User sedang OTP
        if ($request->routeIs('otp.form') || $request->routeIs('otp.verify')) {
            if (session()->has('otp_user_id')) {
                return $next($request);
            }
        }

        return redirect('/login');
    }
}
