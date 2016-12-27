<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomer;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

/**
 * Class CustomersController
 *
 * @package App\Http\Controllers
 */
class CustomersController extends AuthenticatedController
{
    public function index(Request $request)
    {
        $orderBy       = $request->get('order-by') ?: 'name';
        $orderDir      = $request->get('order-dir') ?: 'asc';
        $perPage       = $request->get('per-page') ?: 25;
        $customerQuery = Customer::with('group');

        if ($query = $request->get('query')) {
            $customerQuery = $customerQuery->where(function ($whereSubQuery) use ($query) {
                $whereSubQuery->where('customers.name', 'like', "%{$query}%")
                    ->orWhere('customers.phone', 'like', "%{$query}%")
                    ->orWhere('customers.address', 'like', "%{$query}%");
            });
        }

        if ($groupId = $request->get('group')) {
            $customerQuery = $customerQuery->select('customers.*')
                ->join('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
                ->where('customer_groups.id', '=', $groupId);
        }

        if ($orderBy === 'group') {
            $customers = $customerQuery->select('customers.*')
                ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
                ->orderBy('customer_groups.name', $orderDir)
                ->paginate($perPage);
        } else {
            $customers = $customerQuery->orderBy('customers.'.$orderBy, $orderDir)->paginate($perPage);
        }

        Session::put('last_customer_page', $request->fullUrl());

        return view('customers.index', [
            'customers' => $customers->appends(Input::except('page')),
            'groups'    => CustomerGroup::all(),
            'orderBy'   => $orderBy,
            'orderDir'  => $orderDir,
            'perPage'   => $perPage,
            'headers'   => [
                'name'    => [
                    'label' => 'Name',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'name', 'order-dir' => $orderBy !== 'name' || $orderDir === 'desc' ? 'asc' : 'desc'])
                ],
                'phone'   => [
                    'label' => 'Number',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'phone', 'order-dir' => $orderBy !== 'phone' || $orderDir === 'desc' ? 'asc' : 'desc'])
                ],
                'address' => [
                    'label' => 'Address',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'address', 'order-dir' => $orderBy !== 'address' || $orderDir === 'desc' ? 'asc' : 'desc'])
                ],
                'group'   => [
                    'label' => 'Group (% Discount)',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'group', 'order-dir' => $orderBy !== 'group' || $orderDir === 'desc' ? 'asc' : 'desc'])
                ]
            ]
        ]);
    }

    public function show($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return redirect()->back()->with('flashes.error', 'Customer not found');
        }

        return view('customers.show', [
            'customer' => $customer
        ]);
    }

    public function create()
    {
        return view('customers.create', ['groups' => CustomerGroup::all()]);
    }

    public function store(StoreCustomer $request)
    {
        $newCustomer                       = new Customer();
        $newCustomer->name                 = $request->get('name');
        $newCustomer->phone                = $request->get('phone');
        $newCustomer->email                = $request->get('email');
        $newCustomer->address              = $request->get('address');
        $newCustomer->registered_branch_id = Auth::user()->branch_id;
        $newCustomer->customer_group_id    = $request->get('customer_group_id') ?: null;
        $newCustomer->points               = 0;
        $newCustomer->saveOrFail();

        return redirect(route('customers.index'))->with('flashes.success', 'Customer added');
    }

    public function edit($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return redirect()->back()->with('flashes.error', 'Customer not found');
        }

        return view('customers.edit', [
            'customer' => $customer,
            'groups'   => CustomerGroup::all()
        ]);
    }

    public function update(StoreCustomer $request, $customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return redirect()->back()->with('flashes.error', 'Customer not found');
        }

        $customer->name              = $request->get('name') ?: $customer->name;
        $customer->phone             = $request->get('phone');
        $customer->email             = $request->get('email');
        $customer->address           = $request->get('address');
        $customer->customer_group_id = $request->get('customer_group_id') ?: null;
        $customer->saveOrFail();

        return redirect(Session::get('last_customer_page') ?: route('customers.index'))->with('flashes.success', 'Customer edited');
    }

    public function destroy($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return redirect()->back()->with('flashes.error', 'Customer not found');
        }

        $customer->delete();

        return redirect(Session::get('last_customer_page') ?: route('customers.index'))->with('flashes.success', 'Customer deleted');
    }
}