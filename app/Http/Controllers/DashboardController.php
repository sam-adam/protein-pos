<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Sale;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class DashboardController
 *
 * @package App\Http\Controllers
 */
class DashboardController extends AuthenticatedController
{
    public function index()
    {
        $todayCompletedSales  = Sale::with('payments')
            ->paid()
            ->where('opened_by_user_id', '=', Auth::user()->id)
            ->whereBetween('paid_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
            ->get();
        $incompleteDeliveries = Sale::delivery()
            ->notCancelled()
            ->unPaid()
            ->orderBy('opened_at', 'asc')
            ->get();

        $soonExpired    = [];
        $alreadyExpired = [];
        $branches       = Auth::user()->role === 'sales'
            ? [Auth::user()->branch]
            : Branch::active()->licensed()->get();

        foreach ($branches as $branch) {
            $branch->soonToBeExpired = BranchInventory::with('inventory.product')
                ->inBranch($branch)
                ->where('stock', '>', 0)
                ->join('inventories', 'branch_inventories.inventory_id', '=', 'inventories.id')
                ->where(DB::raw('DATE(inventories.expiry_reminder_date)'), '=', Carbon::now()->toDateString())
                ->get();
            $branch->alreadyExpired  = BranchInventory::with('inventory.product')
                ->inBranch($branch)
                ->where('stock', '>', 0)
                ->join('inventories', 'branch_inventories.inventory_id', '=', 'inventories.id')
                ->where('expired_at', '<', Carbon::now())
                ->get();
        }

        return view('dashboard.index', [
            'branches'             => $branches,
            'incompleteDeliveries' => $incompleteDeliveries,
            'soonExpired'          => $soonExpired,
            'alreadyExpired'       => $alreadyExpired,
            'salesSummary'         => [
                'cash'   => $todayCompletedSales->filter(function (Sale $sale) { return $sale->payments->first()->payment_method === SalePayment::PAYMENT_METHOD_CASH; })->sum(function (Sale $sale) { return $sale->calculateTotal(); }),
                'credit' => $todayCompletedSales->filter(function (Sale $sale) { return $sale->payments->first()->payment_method === SalePayment::PAYMENT_METHOD_CREDIT_CARD; })->sum(function (Sale $sale) { return $sale->calculateTotal(); })
            ]
        ]);
    }
}