<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

/**
 * Class AuthenticatedController
 *
 * @package App\Http\Controllers
 */
abstract class AuthenticatedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        View::share('isExternalWindow', Request::get('external'));
    }
}