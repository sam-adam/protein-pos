<?php

namespace App\Http\Controllers;

use App\DataObjects\Customer as CustomerDataObjects;
use App\DataObjects\ProductWithStock;
use App\Http\Requests\StoreSale;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Shift;
use App\Repository\InventoryRepository;
use App\Services\SaleService;
use Illuminate\Database\Eloquent\Collection;
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
    /** @var InventoryRepository */
    private $inventoryRepo;

    public function __construct(SaleService $saleService, InventoryRepository $inventoryRepo)
    {
        parent::__construct();

        $this->saleService   = $saleService;
        $this->inventoryRepo = $inventoryRepo;
    }

    public function index(Request $request, $salesId = null)
    {
        $oldData = [];
        $sales   = Sale::find($salesId);
        $user    = Auth::user();
        $shift   = Shift::inBranch($user->branch)->open()
            ->where('opened_by_user_id', $user->id)
            ->first();

        if (!$shift) {
            return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
        }

        if ($sales) {
            $oldData = [
                'customerData' => data_get((new CustomerDataObjects($sales->customer))->toArray(), 'customer'),
                'cartData'     => []
            ];

            foreach ($sales->items as $item) {
                $oldData['cartData'][] = new ProductWithStock(
                    $item->product,
                    $this->inventoryRepo->populateProductStock(
                        new Collection([$item->product]),
                        Auth::user()->branch
                    )
                );
            }
        }

        return view('sales.create', array_merge([
            'customerData'  => [],
            'creditCardTax' => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0)
        ], $oldData));
    }

    public function store(StoreSale $request)
    {
        try {
            $sale = DB::transaction(function () use ($request) {
                $customer = Customer::findOrFail($request->get('customer_id'));
                $cashier  = Auth::user();
                $newSale  = $this->saleService->createWalkInSale($customer, $cashier, [
                    'items'          => $request->get('products'),
                    'packages'       => $request->get('packages'),
                    'sales_discount' => $request->get('sales_discount'),
                    'remark'         => $request->get('remark'),
                ]);

                return $this->saleService->finishSale($newSale, Auth::user(), [
                    'method'      => $request->get('payment_method'),
                    'amount'      => $request->get('payment_amount'),
                    'card_number' => $request->get('credit_card_number')
                ]);
            });

            return redirect(route('sales.print', $sale->id))->with([
                'flashes.success' => 'Transaction completed',
                'doPrint'         => true
            ]);
        } catch (\Exception $ex) {
            return redirect()->back()->with('flashes.error', $ex->getMessage());
        }
    }

    public function viewPrint($aleId)
    {
        $sale = Sale::find($aleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        return view('sales.print', [
            'sale'    => $sale,
            'payment' => $sale->payments->first()
        ]);
    }
}