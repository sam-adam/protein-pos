<?php

namespace App\Http\Controllers;

use App\DataObjects\Customer as CustomerDataObjects;
use App\DataObjects\Product as ProductDataObject;
use App\Http\Requests\StoreSale;
use App\Models\Customer;
use App\Models\Product;
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

    public function index()
    {
        return view('sales.index', ['sales' => Sale::paginate()]);
    }

    public function create(Request $request, $salesId = null)
    {
        $oldData         = [];
        $persistentItems = [];
        $sales           = Sale::find($salesId);
        $user            = Auth::user();
        $shift           = Shift::inBranch($user->branch)->open()
            ->where('opened_by_user_id', $user->id)
            ->first();

        if (!$shift) {
            return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
        }

        if ($request->get('type') === 'delivery') {
            $deliveryProductId = Setting::getValueByKey(Setting::KEY_DELIVERY_PRODUCT_ID);

            if (!$deliveryProductId) {
                return redirect(route('settings.index'))->with('flashes.danger', 'Please create a delivery service item');
            }

            $deliveryProduct = Product::find($deliveryProductId);

            if (!$deliveryProduct || !$deliveryProduct->is_service) {
                return redirect(route('settings.index'))->with('flashes.danger', 'Please create a delivery service item');
            }

            $persistentItems[] = new ProductDataObject($deliveryProduct);
        }

        if ($sales) {
            $oldData = [
                'customerData' => data_get((new CustomerDataObjects($sales->customer))->toArray(), 'customer'),
                'cartData'     => []
            ];

            foreach ($sales->items as $item) {
                $oldData['cartData'][] = new ProductDataObject(
                    $item->product,
                    $this->inventoryRepo->populateProductStock(
                        new Collection([$item->product]),
                        Auth::user()->branch
                    )
                );
            }
        }

        return view('sales.create', array_merge([
            'customerData'     => [],
            'creditCardTax'    => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0),
            'persistentItems'  => $persistentItems,
            'immediatePayment' => $request->get('type') !== 'delivery'
        ], $oldData));
    }

    public function store(StoreSale $request)
    {
        try {
            $sale = DB::transaction(function () use ($request) {
                $customer = Customer::findOrFail($request->get('customer_id'));
                $cashier  = Auth::user();
                $newSale  = $this->saleService->createSale($customer, $cashier, [
                    'items'          => $request->get('products'),
                    'packages'       => $request->get('packages'),
                    'sales_discount' => $request->get('sales_discount'),
                    'remark'         => $request->get('remark'),
                    'is_delivery'    => !$request->get('immediate_payment')
                ]);

                if ($request->get('immediate_payment')) {
                    return $this->saleService->finishSale($newSale, Auth::user(), [
                        'method'      => $request->get('payment_method'),
                        'amount'      => $request->get('payment_amount'),
                        'card_number' => $request->get('credit_card_number')
                    ]);
                }

                return $newSale;
            });

            return redirect(route('sales.print', $sale->id))->with([
                'flashes.success' => 'Transaction completed',
                'doPrint'         => true
            ]);
        } catch (\Exception $ex) {
            return redirect()->back()->with('flashes.error', $ex->getMessage());
        }
    }

    public function complete(Request $request, $saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        if ($sale->isPaid()) {
            return redirect()->back()->with('flashes.danger', 'Sale already paid');
        }

        if ($sale->isCancelled()) {
            return redirect()->back()->with('flashes.danger', 'Sale already cancelled');
        }

        DB::transaction(function () use ($sale) {
            return $this->saleService->finishSale($sale, Auth::user(), [
                'method' => 'CASH',
                'amount' => $sale->calculateTotal()
            ]);
        });

        return redirect()->back()->with('flashes.success', 'Sale completed');
    }

    public function viewPrint($aleId)
    {
        $sale = Sale::find($aleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        return view('sales.print', [
            'sale'    => $sale,
            'payment' => $sale->isPaid() ? $sale->payments->first() : null
        ]);
    }
}