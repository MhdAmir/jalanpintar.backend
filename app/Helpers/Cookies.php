<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cookie;

class Cookies
{
    /**
     * Create a refresh token cookie
     *
     * @param string $token
     * @param int $minutes
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function refreshToken(string $token, int $minutes)
    {
        return Cookie::make(
            'refresh_token',
            $token,
            $minutes,
            '/',
            null,
            config('session.secure', false),
            true, // httpOnly
            false,
            config('session.same_site', 'lax')
        );
    }

    /**
     * Forget (delete) the refresh token cookie
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function forgetRefreshToken()
    {
        return Cookie::forget('refresh_token');
    }
}
