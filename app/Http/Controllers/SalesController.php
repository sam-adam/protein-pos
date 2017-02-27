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
use App\Models\User;
use App\Repository\InventoryRepository;
use App\Services\SaleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use PHPExcel_Worksheet_HeaderFooterDrawing;

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
        $sales = Sale::with('refunds')->orderBy('id', 'desc')->paginate();

        return view('sales.index', ['sales' => $sales]);
    }

    public function create(Request $request, $salesId = null)
    {
        $this->authorize('create', Sale::class);

        $canAccessSetting       = Auth::user()->can('access', Setting::class);
        $incompleteSettingRoute = $canAccessSetting ? route('settings.index', ['redirect-to' => $request->fullUrl()]) : url('/');
        $existingData           = [];
        $persistentItems        = [];
        $sales                  = Sale::find($salesId);
        $user                   = User::find(Auth::user()->id);
        $walkInCustomer         = Customer::find(Setting::getValueByKey(Setting::KEY_WALK_IN_CUSTOMER_ID));
        $shift                  = Shift::inBranch($user->branch)->open()->where('opened_by_user_id', $user->id)->first();

        if (!$shift && $user->role === 'cashier') {
            return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
        }

        if ($request->get('type') === 'delivery') {
            $deliveryProductId = Setting::getValueByKey(Setting::KEY_DELIVERY_PRODUCT_ID);

            if (!$deliveryProductId) {
                return redirect($incompleteSettingRoute)->with('flashes.danger', $canAccessSetting ? 'Please create a delivery service item' : 'Please ask your admin to create a delivery service item');
            }

            $deliveryProduct = Product::find($deliveryProductId);

            if (!$deliveryProduct || !$deliveryProduct->is_service) {
                return redirect($incompleteSettingRoute)->with('flashes.danger', $canAccessSetting ? 'Please create a delivery service item' : 'Please ask your admin to create a delivery service item');
            }

            $persistentItems[] = new ProductDataObject($deliveryProduct);
        } elseif (!$walkInCustomer) {
            return redirect($incompleteSettingRoute)->with('flashes.danger', $canAccessSetting ? 'Please choose the default walk in customer' : 'Please ask your admin to choose the default walk in customer');
        }

        if ($sales) {
            $existingData = [
                'customerData' => (new CustomerDataObjects($sales->customer))->toArray(),
                'cartData'     => []
            ];

            foreach ($sales->items as $item) {
                $existingData['cartData'][] = new ProductDataObject(
                    $item->product,
                    $this->inventoryRepo->populateProductStock(
                        new Collection([$item->product]),
                        Auth::user()->branch
                    )
                );
            }
        } elseif ($customer = Customer::find($request->get('customer'))) {
            $existingData['customerData'] = (new CustomerDataObjects($customer))->toArray();
        } elseif ($request->get('type') !== 'delivery') {
            $existingData['customerData'] = (new CustomerDataObjects($walkInCustomer))->toArray();
        }

        return view('sales.create', array_merge([
            'walkInCustomer'      => $walkInCustomer,
            'customerData'        => [],
            'creditCardTax'       => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0),
            'persistentItems'     => $persistentItems,
            'immediatePayment'    => $request->get('type') !== 'delivery',
            'defaultDiscountType' => Sale::DISCOUNT_TYPE_PERCENTAGE,
            'canModifyPrice'      => $user->can('modifyPrice', Sale::class) && $request->get('type') === 'wholesale',
            'isWholesale'         => $request->get('type') === 'wholesale',
            'discount'            => [
                'isEnabled'     => $request->get('type') === 'wholesale'
                    ? $user->can('giveWholeSaleDiscount', Sale::class) && $user->can_give_discount
                    : $user->can_give_discount,
                'isUnlimited'   => $user->can_give_unlimited_discount,
                'maxPrice'      => $user->max_price_discount ?: 0,
                'maxPercentage' => $user->max_percentage_discount ?: 0
            ]
        ], $existingData));
    }

    public function store(StoreSale $request)
    {
        $sale = DB::transaction(function () use ($request) {
            $customer = Customer::findOrFail($request->get('customer_id'));
            $cashier  = Auth::user();
            $newSale  = $this->saleService->createSale($customer, $cashier, [
                'items'               => $request->get('products'),
                'packages'            => $request->get('packages'),
                'sales_discount'      => $request->get('sales_discount'),
                'sales_discount_type' => $request->get('sales_discount_type'),
                'remark'              => $request->get('remark'),
                'is_delivery'         => !$request->get('immediate_payment'),
                'is_wholesale'        => boolval($request->get('is_wholesale'))
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
    }

    public function complete($saleId)
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

    public function cancel($saleId)
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
            return $this->saleService->cancelSale($sale, Auth::user());
        });

        return redirect()->back()->with('flashes.success', 'Sale cancelled');
    }

    public function show($saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        return view('sales.show', ['sale' => $sale]);
    }

    public function viewPrint($saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        return view('sales.print', [
            'sale'    => $sale,
            'payment' => $sale->isPaid() ? $sale->payments->first() : null
        ]);
    }

    public function doPrint($saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        $saleCode = $sale->getCode();

        Excel::load(resource_path('docs/ReceiptTemplate.xlsx'), function (LaravelExcelReader $reader) use ($sale, $saleCode) {
            $startingRow = 14;
            $discountRow = 17;
            $totalRow    = 18;

            $worksheet = $reader->sheet('Sheet1');
            $worksheet->getCell('B6')->setValue($sale->opened_at->format('d m y'));
            $worksheet->getCell('B7')->setValue($sale->opened_at->format('H:i A'));
            $worksheet->getCell('B8')->setValue($saleCode);
            $worksheet->getCell('B9')->setValue($sale->customer->name);
            $worksheet->getCell('B10')->setValue($sale->customer->phone);
            $worksheet->getCell('B11')->setValue($sale->customer->address);

            $currentRow = $startingRow + 1;

            foreach ($sale->getRefundableItems() as $saleItem) {
                $worksheet->prependRow($currentRow, [$saleItem->quantity, $saleItem->product->name, $saleItem->calculateSubTotal()]);

                $currentRow += 1;
            }

            foreach ($sale->getRefundablePackages() as $salePackage) {
                $worksheet->prependRow($currentRow, [$salePackage->quantity, $salePackage->package->name, $salePackage->calculateSubTotal()]);

                $currentRow += 1;
            }

            $worksheet->removeRow($startingRow);

            $worksheet->getCell('C'.($discountRow + ($currentRow - $startingRow - 2)))->setValue($sale->calculateTotal() - $sale->calculateSubTotal());
            $worksheet->getCell('C'.($totalRow + ($currentRow - $startingRow - 2)))->setValue($sale->calculateTotal());
        })->setFileName('receipt-'.$saleCode)->export('xlsx');
    }

    public function viewRefund($saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        $products = [];
        $packages = [];

        foreach ($sale->getRefundableItems() as $saleItem) {
            $dataObject = new \App\DataObjects\Product($saleItem->product);

            array_push($products, [
                'id'               => $saleItem->id,
                'product'          => $dataObject,
                'refundedQuantity' => 0,
                'quantity'         => $saleItem->quantity
            ]);
        }

        foreach ($sale->getRefundablePackages() as $salePackage) {
            $dataObject = new \App\DataObjects\Package($salePackage->package);

            array_push($packages, [
                'id'               => $salePackage->id,
                'package'          => $dataObject,
                'refundedQuantity' => 0,
                'quantity'         => $salePackage->quantity
            ]);
        }

        return view('sales.refund', [
            'sale'          => $sale,
            'products'      => $products,
            'packages'      => $packages,
            'creditCardTax' => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0),
            'customerData'  => (new CustomerDataObjects($sale->customer))->toArray()
        ]);
    }

    public function doRefund(Request $request, $saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            return redirect()->back()->with('flashes.danger', 'Sale not found');
        }

        if (!$sale->isRefundable()) {
            return redirect()->back()->with('flashes.danger', 'Sale can not be refunded');
        }

        DB::transaction(function () use ($sale, $request) {
            $this->saleService->makeRefund($sale, $request->all());
        });

        return redirect(route('sales.print', $sale->id))->with([
            'flashes.success' => 'Refund completed',
            'doPrint'         => true
        ]);
    }
}