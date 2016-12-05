<?php

namespace App\Http\Controllers;


/**
 * Class DashboardController
 *
 * @package App\Http\Controllers
 */
class DashboardController extends AuthenticatedController
{
    public function index()
    {
        return view('dashboard.index');
    }
}