<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class SavePreviousUrl
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('post')) {
            Session::flash('previous_url', Request::url());
        }

        return $next($request);
    }
}