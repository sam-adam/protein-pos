<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        return view('users.create', ['branches' => Branch::licensed()->active()->get()]);
    }

    public function store(StoreUser $request)
    {
        $newUser                              = new User();
        $newUser->name                        = $request->get('name');
        $newUser->username                    = $request->get('username');
        $newUser->password                    = bcrypt($request->get('password'));
        $newUser->branch_id                   = $request->get('branch_id');
        $newUser->role                        = $request->get('role');
        $newUser->max_price_discount          = $request->get('max_price_discount') ?: null;
        $newUser->max_percentage_discount     = $request->get('max_percentage_discount') ?: null;
        $newUser->can_give_discount           = $request->get('can_give_discount') ?: false;
        $newUser->can_give_unlimited_discount = $request->get('can_give_unlimited_discount') ?: false;
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
            'branches' => Branch::licensed()->active()->get()
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

        $user->branch_id                   = $request->get('branch_id');
        $user->role                        = $request->get('role');
        $user->max_price_discount          = $request->get('max_price_discount') ?: $user->max_price_discount;
        $user->max_percentage_discount     = $request->get('max_percentage_discount') ?: $user->max_percentage_discount;
        $user->can_give_discount           = $request->get('can_give_discount') ?: $user->can_give_discount;
        $user->can_give_unlimited_discount = $request->get('can_give_unlimited_discount') ?: $user->can_give_unlimited_discount;
        $user->saveOrFail();

        return redirect(route('users.index'))->with('flashes.success', 'User edited');
    }

    public function destroy($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back()->with('flashes.error', 'User not found');
        }

        if ($userId === Auth::id()) {
            return redirect()->back()->with('flashes.error', 'Cannot delete self');
        }

        $user->delete();

        return redirect(route('users.index'))->with('flashes.success', 'User deleted');
    }
}