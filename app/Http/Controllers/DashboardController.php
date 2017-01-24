<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Auth;

/**
 * Class DashboardController
 *
 * @package App\Http\Controllers
 */
class DashboardController extends AuthenticatedController
{
    public function index()
    {
        $todayCompletedSales  = Sale::with('payments')->paid()->where('opened_by_user_id', '=', Auth::user()->id)->get();
        $incompleteDeliveries = Sale::delivery()
            ->notCancelled()
            ->unPaid()
            ->orderBy('opened_at', 'asc')
            ->get();

        return view('dashboard.index', [
            'incompleteDeliveries' => $incompleteDeliveries,
            'salesSummary'         => [
                'cash'   => $todayCompletedSales->filter(function(Sale $sale) { return $sale->payments->first()->payment_method === SalePayment::PAYMENT_METHOD_CASH; })->sum(function (Sale $sale) { return $sale->calculateTotal(); }),
                'credit' => $todayCompletedSales->filter(function(Sale $sale) { return $sale->payments->first()->payment_method === SalePayment::PAYMENT_METHOD_CREDIT_CARD; })->sum(function (Sale $sale) { return $sale->calculateTotal(); })
            ]
        ]);
    }
}