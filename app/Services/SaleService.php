<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\BranchInventory;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\User;
use App\Repository\InventoryRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SaleService
 *
 * @package App\Services
 */
class SaleService
{
    protected $inventoryRepo;
    protected $inventoryService;

    public function __construct(InventoryRepository $inventoryRepo, InventoryService $inventoryService)
    {
        $this->inventoryRepo    = $inventoryRepo;
        $this->inventoryService = $inventoryService;
    }

    public function createWalkInSale(Customer $customer, User $openedBy, array $saleData)
    {
        $newSale                    = new Sale();
        $newSale->opened_at         = Carbon::now();
        $newSale->opened_by_user_id = $openedBy;
        $newSale->branch_id         = $openedBy->branch_id;
        $newSale->customer_id       = $customer->id;
        $newSale->customer_discount = $customer->group ? $customer->group->discount : 0;
        $newSale->sales_discount    = data_get($saleData, 'sales_discount', 0);
        $newSale->is_delivery       = false;
        $newSale->remark            = data_get($saleData, 'remark');
        $newSale->total             = 0;
        $newSale->saveOrFail();

        $newSale->items()->initRelation([], 'items');

        foreach (data_get($saleData, 'items', []) as $item) {
            $product           = Product::findOrFail(data_get($item, 'id'));
            $requestedQuantity = data_get($item, 'quantity');
            $addedQuantity     = 0;

            if (!$this->inventoryRepo->checkIfStockSufficient($product, $requestedQuantity)) {
                throw new InsufficientStockException($product, $requestedQuantity);
            }

            while ($addedQuantity < $requestedQuantity) {
                $stillRequiredQuantity = $requestedQuantity - $addedQuantity;
                $branchInventory       = BranchInventory::notEmpty()
                    ->inBranch($openedBy->branch)
                    ->product($product)
                    ->orderBy('priority', 'asc')
                    ->first();

                if (!$branchInventory) {
                    throw new InsufficientStockException($product, $requestedQuantity);
                }

                $availableQuantity = min($stillRequiredQuantity, $branchInventory->stock);

                // adjust stock
                $branchInventory->stock -= $availableQuantity;
                $branchInventory->saveOrFail();

                // save sale item
                $newSaleItem                      = new SaleItem();
                $newSaleItem->sale_id             = $newSale->id;
                $newSaleItem->branch_inventory_id = $branchInventory->id;
                $newSaleItem->quantity            = $availableQuantity;
                $newSaleItem->price               = data_get($item, 'price', $product->price);
                $newSaleItem->original_price      = $product->price;
                $newSaleItem->discount            = data_get($item, 'discount', 0);
                $newSaleItem->subtotal            = $newSaleItem->calculateSubTotal();
                $newSaleItem->saveOrFail();

                $newSale->items->push($newSaleItem);

                $addedQuantity += $availableQuantity;
            }

            // save the total sale
            $newSale->total = $newSale->calculateTotal();
            $newSale->saveOrFail();

            // reorder priority
            $this->inventoryService->reOrderPriority($product, $openedBy->branch);
        }

        return $newSale;
    }

    public function finishSale(Sale $sale, User $closedBy, array $paymentData)
    {
        if ($sale->isFinished()) {
            return $sale;
        }

        $alreadyPaidAmount = 0;
        $existingPayments  = $sale->payments;

        foreach ($existingPayments as $payment) {
            if ($payment->payment_method === SalePayment::PAYMENT_METHOD_CREDIT_CARD) {
                return $sale;
            }

            $alreadyPaidAmount += $payment->amount;
        }

        if ($alreadyPaidAmount > $sale->total) {
            return $sale;
        }

        $newPayment                 = new SalePayment();
        $newPayment->sale_id        = $sale->id;
        $newPayment->payment_method = data_get($paymentData, 'method');
        $newPayment->amount         = $newPayment->payment_method !== SalePayment::PAYMENT_METHOD_CASH
            ? $sale->total - $alreadyPaidAmount
            : data_get($paymentData, 'amount');
        $newPayment->amount         = data_get($paymentData, 'amount');

        if ($newPayment->payment_method === SalePayment::PAYMENT_METHOD_CREDIT_CARD) {
            $newPayment->card_number = data_get($paymentData, 'card_number');
            $newPayment->card_tax    = Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0);
        }

        $newPayment->saveOrFail();

        $sale->paid_at           = Carbon::now();
        $sale->closed_at         = Carbon::now();
        $sale->closed_by_user_id = $closedBy->id;
        $sale->saveOrFail();

        return $sale;
    }
}