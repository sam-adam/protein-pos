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
    public function __construct()
    {
        parent::__construct();

        $this->middleware('can:access,'.User::class);
    }

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
        $newUser->can_do_refund               = $request->get('can_do_refund', false);
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
        $user->max_price_discount          = $request->get('max_price_discount', 0);
        $user->max_percentage_discount     = $request->get('max_percentage_discount', 0);
        $user->can_give_discount           = $request->get('can_give_discount', false);
        $user->can_give_unlimited_discount = $request->get('can_give_unlimited_discount', false);
        $user->can_do_refund               = $request->get('can_do_refund', false);
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