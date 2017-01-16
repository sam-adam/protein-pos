<?php

namespace App\Http\Controllers;

use App\Models\Sale;

/**
 * Class DashboardController
 *
 * @package App\Http\Controllers
 */
class DashboardController extends AuthenticatedController
{
    public function index()
    {
        $incompleteDeliveries = Sale::delivery()
            ->notCancelled()
            ->unPaid()
            ->orderBy('opened_at', 'asc')
            ->get();

        return view('dashboard.index', [
            'incompleteDeliveries' => $incompleteDeliveries
        ]);
    }
}