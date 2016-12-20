<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

/**
 * Class InventoryMovementsController
 *
 * @package App\Http\Controllers
 */
class InventoryMovementsController extends AuthenticatedController
{
    public function index()
    {
        return view('inventoryMovements.index');
    }

    public function create()
    {
        return view('inventoryMovements.create', [
            'otherBranches' => Branch::licensed()->active()->get()->except(Auth::user()->branch_id),
        ]);
    }
}