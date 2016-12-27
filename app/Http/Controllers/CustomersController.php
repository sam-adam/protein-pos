<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

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
            $customerQuery =  $customerQuery->where(function ($whereSubQuery) use ($query) {
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

    public function show() { }

    public function create() { }

    public function store() { }

    public function edit() { }

    public function update() { }

    public function destroy() { }
}