<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranch;
use App\Models\Branch;
use Carbon\Carbon;

/**
 * Class BranchesController
 *
 * @package App\Http\Controllers
 */
class BranchesController extends AuthenticatedController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('can:access,'.Branch::class);
    }

    public function index()
    {
        return view('branches.index', ['branches' => Branch::paginate()]);
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(StoreBranch $request)
    {
        $newBranch                        = new Branch();
        $newBranch->name                  = $request->get('name');
        $newBranch->address               = $request->get('address');
        $newBranch->contact_person_name   = $request->get('contact_person_name');
        $newBranch->contact_person_phone  = $request->get('contact_person_phone');
        $newBranch->cash_counters_count   = $request->get('cash_counters_count');
        $newBranch->saveOrFail();

        return redirect(route('branches.index'))->with('flashes.success', 'Branch added');
    }

    public function edit($branchId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return redirect()->back()->with('flashes.error', 'Branch not found');
        }

        return view('branches.edit', ['branch' => $branch]);
    }

    public function update(StoreBranch $request, $branchId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return redirect()->back()->with('flashes.error', 'Branch not found');
        }

        $branch->name                  = $request->get('name');
        $branch->address               = $request->get('address');
        $branch->contact_person_name   = $request->get('contact_person_name');
        $branch->contact_person_phone  = $request->get('contact_person_phone');
        $branch->cash_counters_count   = $request->get('cash_counters_count');
        $branch->saveOrFail();

        return redirect(route('branches.index'))->with('flashes.success', 'Branch edited');
    }

    public function license($branchId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return redirect()->back()->with('flashes.error', 'Branch not found');
        } elseif ($branch->isLicensed()) {
            return redirect()->back()->with('flashes.error', 'Branch already licensed');
        }

        $branch->licensed_at = Carbon::now();
        $branch->saveOrFail();

        return redirect(route('branches.index'))->with('flashes.success', 'Branch licensed');
    }

    public function activate($branchId)
    {
        $branch = Branch::find($branchId);

        if (!$branch) {
            return redirect()->back()->with('flashes.error', 'Branch not found');
        } elseif (!$branch->isLicensed()) {
            return redirect()->back()->with('flashes.error', 'Branch is not licensed');
        } elseif ($branch->isActive()) {
            return redirect()->back()->with('flashes.error', 'Branch already activated');
        }

        $branch->activated_at = Carbon::now();
        $branch->saveOrFail();

        return redirect(route('branches.index'))->with('flashes.success', 'Branch activated');
    }
}