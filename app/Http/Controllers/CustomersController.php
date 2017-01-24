<?php

namespace App\Http\Controllers;

use App\DataObjects\CollectionDataObject;
use App\Http\Requests\StoreCustomer;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

/**
 * Class CustomersController
 *
 * @package App\Http\Controllers
 */
class CustomersController extends AuthenticatedController
{
    protected function getCustomersQuery(Request $request)
    {
        $perPage       = $request->get('per-page') ?: 25;
        $orderBy       = $request->get('order-by') ?: 'name';
        $orderDir      = $request->get('order-dir') ?: 'asc';
        $customerQuery = Customer::with('group');

        if ($query = $request->get('query')) {
            $customerQuery = $customerQuery->where(function ($whereSubQuery) use ($query) {
                $whereSubQuery->where('customers.name', 'like', "%{$query}%")
                    ->orWhere('customers.phone', 'like', "%{$query}%")
                    ->orWhere('customers.address', 'like', "%{$query}%")
                    ->orWhere('customers.email', 'like', "%{$query}%");
            });
        }

        if ($groupId = $request->get('group')) {
            $customerQuery = $customerQuery->select('customers.*')
                ->join('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
                ->where('customer_groups.id', '=', $groupId);
        }

        if ($orderBy === 'group') {
            $customerQuery = $customerQuery->select('customers.*')
                ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
                ->orderBy('customer_groups.name', $orderDir);
        } else {
            $customerQuery = $customerQuery->orderBy('customers.'.$orderBy, $orderDir);
        }

        return [
            'query'    => $customerQuery,
            'perPage'  => $perPage,
            'orderBy'  => $orderBy,
            'orderDir' => $orderDir
        ];
    }

    public function index(Request $request)
    {
        Session::put('last_customer_page', $request->fullUrl());

        $customerQuery = $this->getCustomersQuery($request);
        $customers     = $customerQuery['query']->paginate();
        $customersJson = new Collection();
        $perPage       = $customerQuery['perPage'];
        $orderBy       = $customerQuery['orderBy'];
        $orderDir      = $customerQuery['orderDir'];

        foreach ($customers as $customer) {
            $customersJson[$customer->id] = new \App\DataObjects\Customer($customer);
        }

        return view('customers.index', [
            'customers'     => $customers->appends(Input::except('page')),
            'customersJson' => $customersJson,
            'groups'        => CustomerGroup::all(),
            'orderBy'       => $orderBy,
            'orderDir'      => $orderDir,
            'perPage'       => $perPage,
            'intent'        => $request->get('intent', 'display'),
            'headers'       => [
                'name'    => [
                    'label' => 'Name',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'name', 'order-dir' => $orderBy !== 'name' || $orderDir === 'desc' ? 'asc' : 'desc'])
                ],
                'email'   => [
                    'label' => 'E-Mail',
                    'url'   => $request->fullUrlWithQuery(['order-by' => 'email', 'order-dir' => $orderBy !== 'email' || $orderDir === 'desc' ? 'asc' : 'desc'])
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
                    'label' => 'Group (% Disc)',
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
            'customer' => $customer,
            'sales'    => Sale::where('customer_id', '=', $customer->id)
                ->orderBy('opened_at', 'desc')
                ->get()
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

    public function bulkChangeGroup(Request $request)
    {
        if ($request->get('customer_group_id')) {
            $group = CustomerGroup::find($request->get('customer_group_id'));

            if (!$group) {
                return redirect()->back()->with('flashes.error', 'Customer group not found');
            }
        }

        foreach ($request->get('customer_ids') as $customerId) {
            if ($customer = Customer::find($customerId)) {
                $customer->customer_group_id = isset($group) ? $group->id : null;
                $customer->saveOrFail();
            }
        }

        return redirect(Session::get('last_customer_page') ?: route('customers.index'))->with('flashes.success', 'Customers group updated');
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->get('customer_ids') as $customerId) {
            if ($customer = Customer::find($customerId)) {
                $customer->delete();
            }
        }

        return redirect(Session::get('last_customer_page') ?: route('customers.index'))->with('flashes.success', 'Customers deleted');
    }

    public function exportAsCsv(Request $request)
    {
        Excel::create('customers-'.Carbon::now()->format('YmdHis'), function (LaravelExcelWriter $excel) use ($request) {
            $excel->sheet('list', function (LaravelExcelWorksheet $sheet) use ($request) {
                $customers = $request->has('all') ? Customer::all() : $this->getCustomersQuery($request)['query']->get();

                $sheet->fromArray($customers->map(function (Customer $customer) {
                    return [
                        'Name'    => $customer->name,
                        'E-Mail'  => $customer->email,
                        'Phone'   => $customer->phone,
                        'Address' => $customer->address,
                        'Group'   => $customer->group ? $customer->group->name.' ('.$customer->group->discount.'%)' : ''
                    ];
                })->toArray());
                $sheet->setColumnFormat(['A:Z' => '@']);
            });
        })->download('csv');
    }

    public function xhrSearch(Request $request)
    {
        $query      = $request->query('query');
        $collection = new CollectionDataObject();
        $customers  = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($customers as $customer) {
            $dataObject = new \App\DataObjects\Customer($customer);
            $collection->add($dataObject);
        }

        $collection->setKey('customers');

        return response()->json($collection);
    }
}