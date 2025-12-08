<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthStatusController extends Controller
{
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::check() ? Auth::user()->only(['id', 'name', 'email']) : null,
            'session' => [
                'id' => session()->getId(),
                'has_token' => session()->has('_token'),
                'token' => session()->token(),
            ],
            'cookies' => [
                'has_xsrf_token' => $request->cookies->has('XSRF-TOKEN'),
                'has_laravel_session' => $request->cookies->has(config('session.cookie')),
            ],
            'headers' => [
                'has_x_xsrf_token' => $request->headers->has('X-XSRF-TOKEN'),
                'x_requested_with' => $request->headers->get('X-Requested-With'),
                'accept' => $request->headers->get('Accept'),
            ],
        ]);
    }
}
