<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Class UsersController
 *
 * @package App\Http\Controllers
 */
class UsersController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::paginate()]);
    }
}