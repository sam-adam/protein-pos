<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Models\Branch;
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

    public function create()
    {
        return view('users.create', ['branches' => Branch::all()]);
    }

    public function store(StoreUser $request)
    {
        $newUser                        = new User();
        $newUser->name                  = $request->get('name');
        $newUser->username              = $request->get('username');
        $newUser->password              = bcrypt($request->get('password'));
        $newUser->branch_id             = $request->get('branch_id');
        $newUser->minimum_discount      = $request->get('minimum_discount') ?: null;
        $newUser->minimum_discount_type = $request->get('minimum_discount_type')  ?: null;
        $newUser->maximum_discount      = $request->get('maximum_discount') ?: null;
        $newUser->maximum_discount_type = $request->get('maximum_discount_type') ?: null;
        $newUser->saveOrFail();

        return redirect(route('users.index'))->with('flashes.success', 'User added');
    }
}