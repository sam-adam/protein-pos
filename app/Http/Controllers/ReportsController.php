<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class ReportsController
 *
 * @package App\Http\Controllers
 */
class ReportsController extends AuthenticatedController
{
    public function sales(Request $request)
    {
        $branch = Branch::find($request->get('branch'));
        $from   = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->timestamp)->startOfDay();
        $to     = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $sales  = !$branch ?: Sale::where('branch_id', '=', $branch->id)
            ->finished()
            ->paid()
            ->whereBetween('opened_at', [$from, $to])
            ->get();

        return view('reports.sales', [
            'branchId' => $request->get('branch'),
            'branches' => Branch::orderBy('name', 'asc')->get(),
            'sales'    => $sales,
            'from'     => $from,
            'to'       => $to
        ]);
    }

    public function stock() { }

    public function product() { }
}