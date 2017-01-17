<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        $mode   = $request->get('mode') ?: 'daily';

        if (!$branch) {
            $sales = new Collection();
        } else {
            $sales = Sale::where('branch_id', '=', $branch->id)
                ->finished()
                ->paid()
                ->whereBetween('opened_at', [$from, $to])
                ->get();
        }

        return view('reports.sales', [
            'branchId' => $request->get('branch'),
            'branches' => Branch::orderBy('name', 'asc')->get(),
            'sales'    => $sales,
            'from'     => $from,
            'to'       => $to,
            'mode'     => $mode
        ]);
    }

    public function stock() { }

    public function product() { }
}