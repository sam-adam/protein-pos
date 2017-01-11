<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSale;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Shift;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class SalesController
 *
 * @package App\Http\Controllers
 */
class SalesController extends AuthenticatedController
{
    /** @var SaleService */
    private $saleService;

    public function __construct(SaleService $saleService)
    {
        parent::__construct();

        $this->saleService = $saleService;
    }

    public function index(Request $request)
    {
        $user  = Auth::user();
        $shift = Shift::inBranch($user->branch)->open()
            ->where('opened_by_user_id', $user->id)
            ->first();

        if (!$shift) {
            return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
        }

        return view('sales.create', [
            'creditCardTax' => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0)
        ]);
    }

    public function store(StoreSale $request)
    {
        try {
            $sale = DB::transaction(function () use ($request) {
                $customer = Customer::findOrFail($request->get('customer_id'));
                $cashier  = Auth::user();
                $newSale  = $this->saleService->createWalkInSale($customer, $cashier, [
                    'items'          => $request->get('products'),
                    'sales_discount' => $request->get('sales_discount'),
                    'remark'         => $request->get('remark'),
                ]);

                return $this->saleService->finishSale($newSale, Auth::user(), [
                    'method'      => $request->get('payment_method'),
                    'amount'      => $request->get('payment_amount'),
                    'card_number' => $request->get('credit_card_number')
                ]);
            });

            return redirect(route('sales.print', $sale->id))->with('flashes.success', 'Transaction completed');
        } catch (\Exception $ex) {
            return redirect()->back()->with('flashes.error', $ex->getMessage());
        }
    }

    public function viewPrint()
    {
    }
}