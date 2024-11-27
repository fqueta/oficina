<?php

namespace App\Http\Middleware;

use App\Qlib\Qlib;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
            // return redirect()->guest('admin/auth/login');
            // return 'admin';
            // if (\Auth::user()->isAdmin())
            //     return 'admin/';
            // else if (\Auth::user()->isProvider())
            //     return 'provider/';
            // else
            //     return '/';
        }
    }
}
