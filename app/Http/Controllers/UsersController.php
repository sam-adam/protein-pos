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
class UsersController extends AuthenticatedController
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
        $newUser->role                  = $request->get('role');
        $newUser->minimum_discount      = $request->get('minimum_discount') ?: null;
        $newUser->minimum_discount_type = $request->get('minimum_discount_type') ?: null;
        $newUser->maximum_discount      = $request->get('maximum_discount') ?: null;
        $newUser->maximum_discount_type = $request->get('maximum_discount_type') ?: null;
        $newUser->saveOrFail();

        return redirect(route('users.index'))->with('flashes.success', 'User added');
    }

    public function edit($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back()->with('flashes.error', 'User not found');
        }

        return view('users.edit', [
            'user'     => $user,
            'branches' => Branch::all()
        ]);
    }

    public function update(StoreUser $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back()->with('flashes.error', 'User not found');
        }

        if ($request->get('password')) {
            $user->password = bcrypt($request->get('password'));
        }

        $user->branch_id             = $request->get('branch_id');
        $user->role                  = $request->get('role');
        $user->minimum_discount      = $request->get('minimum_discount') ?: null;
        $user->minimum_discount_type = $request->get('minimum_discount_type') ?: null;
        $user->maximum_discount      = $request->get('maximum_discount') ?: null;
        $user->maximum_discount_type = $request->get('maximum_discount_type') ?: null;

        $user->saveOrFail();

        return redirect(route('users.index'))->with('flashes.success', 'User edited');
    }
}