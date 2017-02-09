<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePackage;
use App\Models\SalePackageItem;
use App\Models\SalePayment;
use App\Models\SaleRefund;
use App\Models\SaleRefundItem;
use App\Models\SaleRefundPackage;
use App\Models\Setting;
use App\Models\User;
use App\Repository\InventoryRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Class SaleService
 *
 * @package App\Services
 */
class SaleService
{
    protected $inventoryRepo;
    protected $inventoryService;
    protected $pointService;

    public function __construct(InventoryRepository $inventoryRepo, InventoryService $inventoryService, PointService $pointService)
    {
        $this->inventoryRepo    = $inventoryRepo;
        $this->inventoryService = $inventoryService;
        $this->pointService     = $pointService;
    }

    /**
     * Create a new sale
     *
     * @param Customer $customer
     * @param User     $openedBy
     * @param array    $saleData
     *
     * @return \App\Models\Sale
     */
    public function createSale(Customer $customer, User $openedBy, array $saleData)
    {
        $newSale                      = new Sale();
        $newSale->opened_at           = Carbon::now();
        $newSale->opened_by_user_id   = $openedBy->id;
        $newSale->branch_id           = $openedBy->branch_id;
        $newSale->customer_id         = $customer->id;
        $newSale->customer_discount   = $customer->group ? $customer->group->discount : 0;
        $newSale->sales_discount      = data_get($saleData, 'sales_discount', 0);
        $newSale->sales_discount_type = data_get($saleData, 'sales_discount_type', Sale::DISCOUNT_TYPE_PERCENTAGE);
        $newSale->is_delivery         = data_get($saleData, 'is_delivery', false);
        $newSale->is_wholesale        = data_get($saleData, 'is_wholesale', false);
        $newSale->remark              = data_get($saleData, 'remark');
        $newSale->total               = 0;
        $newSale->saveOrFail();

        $newSale->setRelation('items', new Collection());
        $newSale->setRelation('packages', new Collection());

        foreach (data_get($saleData, 'items', []) ?: [] as $item) {
            $product           = Product::findOrFail(data_get($item, 'id'));
            $requestedQuantity = data_get($item, 'quantity');

            if ($product->is_service) {
                $newSaleItem                 = new SaleItem();
                $newSaleItem->sale_id        = $newSale->id;
                $newSaleItem->product_id     = $product->id;
                $newSaleItem->quantity       = $requestedQuantity;
                $newSaleItem->price          = data_get($item, 'price', $product->price);
                $newSaleItem->original_price = $product->price;
                $newSaleItem->discount       = data_get($item, 'discount', 0);
                $newSaleItem->subtotal       = $newSaleItem->calculateSubTotal();
                $newSaleItem->saveOrFail();

                $newSale->items->push($newSaleItem);
            } else {
                $usedInventories = $this->useInventory($product, $openedBy->branch, $requestedQuantity);

                foreach ($usedInventories as $usedInventory) {
                    // save sale item
                    $newSaleItem                      = new SaleItem();
                    $newSaleItem->sale_id             = $newSale->id;
                    $newSaleItem->product_id          = $product->id;
                    $newSaleItem->branch_inventory_id = $usedInventory['branchInventory']->id;
                    $newSaleItem->quantity            = $usedInventory['availableQuantity'];
                    $newSaleItem->price               = data_get($item, 'price', $product->price);
                    $newSaleItem->original_price      = $product->price;
                    $newSaleItem->discount            = data_get($item, 'discount', 0);
                    $newSaleItem->subtotal            = $newSaleItem->calculateSubTotal();
                    $newSaleItem->saveOrFail();

                    $newSale->items->push($newSaleItem);
                }
            }

            // reorder priority
            $this->inventoryService->reOrderPriority($product, $openedBy->branch);
        }

        // save the total sale
        $newSale->total = $newSale->calculateTotal();
        $newSale->saveOrFail();

        foreach (data_get($saleData, 'packages', []) ?: [] as $requestedPackage) {
            $package           = Package::findOrFail(data_get($requestedPackage, 'id'));
            $requestedQuantity = data_get($requestedPackage, 'quantity');

            // create new sale package
            $newSalePackage                 = new SalePackage();
            $newSalePackage->sale_id        = $newSale->id;
            $newSalePackage->package_id     = $package->id;
            $newSalePackage->quantity       = $requestedQuantity;
            $newSalePackage->price          = $package->price;
            $newSalePackage->original_price = $package->price;
            $newSalePackage->discount       = data_get($requestedPackage, 'discount', 0);
            $newSalePackage->subtotal       = $newSalePackage->calculateSubTotal();
            $newSalePackage->saveOrFail();

            $newSalePackage->setRelation('items', new Collection());

            foreach ($package->items as $packageItem) {
                $product           = Product::findOrFail($packageItem->product_id);
                $requestedQuantity = $packageItem->quantity;

                // get the branch inventories
                $usedInventories = $this->useInventory($product, $openedBy->branch, $requestedQuantity * $packageItem->quantity);

                foreach ($usedInventories as $usedInventory) {
                    $newSalePackageItem                      = new SalePackageItem();
                    $newSalePackageItem->sale_package_id     = $newSalePackage->id;
                    $newSalePackageItem->product_id          = $product->id;
                    $newSalePackageItem->branch_inventory_id = $usedInventory['branchInventory']->id;
                    $newSalePackageItem->quantity            = $usedInventory['availableQuantity'];
                    $newSalePackageItem->original_price      = $product->price;
                    $newSalePackageItem->saveOrFail();

                    $newSalePackage->items->push($newSalePackageItem);
                }

                // reorder priority
                $this->inventoryService->reOrderPriority($product, $openedBy->branch);
            }

            if ($package->is_customizable) {
                foreach ($package->variants as $packageVariant) {
                    if (!isset($requestedPackage['variants'][$packageVariant->product_variant_group_id])) {
                        throw new ModelNotFoundException(PackageVariant::class);
                    }

                    foreach ($requestedPackage['variants'][$packageVariant->product_variant_group_id] as $variantProductId => $variantProductRequestData) {
                        if ($variantProductRequestData['quantity'] > 0) {
                            $variantProduct  = Product::findOrFail($variantProductId);
                            $usedInventories = $this->useInventory($variantProduct, $openedBy->branch, $requestedQuantity * $variantProductRequestData['quantity']);

                            foreach ($usedInventories as $usedInventory) {
                                $newSalePackageItem                           = new SalePackageItem();
                                $newSalePackageItem->sale_package_id          = $newSalePackage->id;
                                $newSalePackageItem->product_id               = $variantProduct->id;
                                $newSalePackageItem->branch_inventory_id      = $usedInventory['branchInventory']->id;
                                $newSalePackageItem->quantity                 = $usedInventory['availableQuantity'];
                                $newSalePackageItem->original_price           = $variantProduct->price;
                                $newSalePackageItem->product_variant_group_id = $packageVariant->product_variant_group_id1;
                                $newSalePackageItem->saveOrFail();

                                $newSalePackage->items->push($newSalePackageItem);
                            }

                            $this->inventoryService->reOrderPriority($variantProduct, $openedBy->branch);
                        }
                    }
                }
            }

            $newSale->packages->push($newSalePackage);
        }

        return $newSale;
    }

    /**
     * Get branch inventories
     *
     * @param Product $product
     * @param Branch  $inBranch
     * @param int     $requestedQuantity
     *
     * @return \Illuminate\Support\Collection
     */
    private function useInventory(Product $product, Branch $inBranch, $requestedQuantity)
    {
        $usedInventories = new Collection();
        $addedQuantity   = 0;

        if (!$this->inventoryRepo->checkIfStockSufficient($product, $requestedQuantity)) {
            throw new InsufficientStockException($product, $requestedQuantity);
        }

        while ($addedQuantity < $requestedQuantity) {
            $stillRequiredQuantity = $requestedQuantity - $addedQuantity;
            $branchInventory       = BranchInventory::notEmpty()
                ->inBranch($inBranch)
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

            $this->inventoryService->adjustContainerStock($branchInventory, $availableQuantity, InventoryService::MOVEMENT_TYPE_SUBTRACTION);

            $addedQuantity += $availableQuantity;

            $usedInventories->push([
                'branchInventory'   => $branchInventory,
                'availableQuantity' => $availableQuantity
            ]);
        }

        return $usedInventories;
    }

    /**
     * Mark the payment of a sale
     *
     * @param Sale  $sale
     * @param User  $closedBy
     * @param array $paymentData
     *
     * @return \App\Models\Sale
     */
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

        if (strtoupper($newPayment->payment_method) === SalePayment::PAYMENT_METHOD_CREDIT_CARD) {
            $newPayment->card_number = data_get($paymentData, 'card_number');
            $newPayment->card_tax    = Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0);
        }

        $newPayment->saveOrFail();

        if ($sale->is_delivery) {
            $sale->delivered_at = Carbon::now();
        }

        $sale->paid_at           = Carbon::now();
        $sale->closed_at         = Carbon::now();
        $sale->closed_by_user_id = $closedBy->id;
        $sale->saveOrFail();

        // adjust customer points
        $earnedPoints = $this->pointService->calculatePointsEarned($sale);

        $sale->earned_points = $earnedPoints;
        $sale->saveOrFail();

        $sale->customer->points += $earnedPoints;
        $sale->customer->saveOrFail();

        return $sale;
    }

    /**
     * Cancel a pending sale
     *
     * @param Sale $sale
     * @param User $cancelledBy
     *
     * @return Sale
     */
    public function cancelSale(Sale $sale, User $cancelledBy)
    {
        if ($sale->isCancelled()) {
            return $sale;
        }

        foreach ($sale->items as $saleItem) {
            if (!$saleItem->product->is_service) {
                $branchInventory = BranchInventory::findOrFail($saleItem->branch_inventory_id);
                $branchInventory->quantity += $saleItem->quantity;
                $branchInventory->saveOrFail();

                $this->inventoryService->adjustContainerStock($branchInventory, $saleItem->quantity, InventoryService::MOVEMENT_TYPE_ADDITION);
            }
        }

        foreach ($sale->packages as $salePackage) {
            foreach ($salePackage->items as $packageItem) {
                $branchInventory = BranchInventory::findOrFail($packageItem->branch_inventory_id);
                $branchInventory->quantity += $packageItem->quantity;
                $branchInventory->saveOrFail();

                $this->inventoryService->adjustContainerStock($branchInventory, $packageItem->quantity, InventoryService::MOVEMENT_TYPE_ADDITION);
            }
        }

        $sale->cancelled_at      = Carbon::now();
        $sale->cancelled_by      = $cancelledBy->id;
        $sale->closed_at         = Carbon::now();
        $sale->closed_by_user_id = $cancelledBy->id;
        $sale->saveOrFail();

        return $sale;
    }

    /**
     * Create a refund
     *
     * @param Sale  $sale
     * @param array $refundedItems
     *
     * @return SaleRefund
     */
    public function makeRefund(Sale $sale, array $refundedItems)
    {
        $beforeRefundTotal  = $sale->calculateTotal();
        $newRefund          = new SaleRefund();
        $newRefund->sale_id = $sale->id;
        $newRefund->total   = 0;
        $newRefund->saveOrFail();

        foreach (data_get($refundedItems, 'products') as $saleItemId => $data) {
            $saleItem = SaleItem::findOrFail($saleItemId);

            if ($data['quantity'] > 0) {
                $newRefundItem                 = new SaleRefundItem();
                $newRefundItem->sale_refund_id = $newRefund->id;
                $newRefundItem->sale_item_id   = $saleItemId;
                $newRefundItem->quantity       = $data['quantity'];
                $newRefundItem->saveOrFail();
            }

            if (!$saleItem->product->is_service) {
                $branchInventory = BranchInventory::findOrFail($saleItem->branch_inventory_id);
                $branchInventory->quantity += $saleItem->quantity;
                $branchInventory->saveOrFail();

                $this->inventoryService->adjustContainerStock($branchInventory, $saleItem->quantity, InventoryService::MOVEMENT_TYPE_ADDITION);
            }
        }

        foreach (data_get($refundedItems, 'packages') as $salePackageId => $data) {
            $salePackage = SalePackage::findOrFail($salePackageId);

            if ($data['quantity'] > 0) {
                $newRefundPackage                  = new SaleRefundPackage();
                $newRefundPackage->sale_refund_id  = $newRefund->id;
                $newRefundPackage->sale_package_id = $salePackageId;
                $newRefundPackage->quantity        = $data['quantity'];
                $newRefundPackage->saveOrFail();
            }

            foreach ($salePackage->items as $packageItem) {
                $branchInventory = BranchInventory::findOrFail($packageItem->branch_inventory_id);
                $branchInventory->quantity += $packageItem->quantity;
                $branchInventory->saveOrFail();

                $this->inventoryService->adjustContainerStock($branchInventory, $packageItem->quantity, InventoryService::MOVEMENT_TYPE_ADDITION);
            }
        }

        $newRefund->total = $beforeRefundTotal - $sale->load('refunds')->calculateTotal();
        $newRefund->saveOrFail();

        return $newRefund;
    }
}