<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerGroup;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\DB;

/**
 * Class CustomerGroupsController
 *
 * @package App\Http\Controllers
 */
class CustomerGroupsController extends AuthenticatedController
{
    public function index()
    {
        return view('customerGroups.index', ['groups' => CustomerGroup::paginate()]);
    }

    public function show()
    {
    }

    public function create()
    {
        return view('customerGroups.create');
    }

    public function store(StoreCustomerGroup $request)
    {
        $newCustomerGroup           = new CustomerGroup();
        $newCustomerGroup->name     = $request->get('name');
        $newCustomerGroup->discount = $request->get('discount');
        $newCustomerGroup->saveOrFail();

        return redirect(route('customer-groups.index'))->with('flashes.success', 'Brand added');
    }

    public function edit($groupId)
    {
        $group = CustomerGroup::findOrFail($groupId);

        if (!$group) {
            return redirect()->back()->with('flashes.error', 'Customer group not found');
        }

        return view('customerGroups.edit', ['group' => $group]);
    }

    public function update(StoreCustomerGroup $request, $groupId)
    {
        $group = CustomerGroup::findOrFail($groupId);

        if (!$group) {
            return redirect()->back()->with('flashes.error', 'Customer group not found');
        }

        $group->name     = $request->get('name');
        $group->discount = $request->get('discount');
        $group->saveOrFail();

        return redirect(route('customer-groups.index'))->with('flashes.success', 'Customer group edited');
    }

    public function destroy($groupId)
    {
        $group = CustomerGroup::findOrFail($groupId);

        if (!$group) {
            return redirect()->back()->with('flashes.error', 'Customer group not found');
        }

        DB::transaction(function () use ($group) {
            foreach ($group->customers as $customer) {
                $customer->customer_group_id = null;
                $customer->saveOrFail();
            }

            $group->delete();
        });

        return redirect(route('customer-groups.index'))->with('flashes.success', 'Customer group deleted');
    }
}