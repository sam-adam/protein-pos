<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePackage;
use App\Models\SalePackageItem;
use App\Models\SalePayment;
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
        $newSale                    = new Sale();
        $newSale->opened_at         = Carbon::now();
        $newSale->opened_by_user_id = $openedBy->id;
        $newSale->branch_id         = $openedBy->branch_id;
        $newSale->customer_id       = $customer->id;
        $newSale->customer_discount = $customer->group ? $customer->group->discount : 0;
        $newSale->sales_discount    = data_get($saleData, 'sales_discount', 0);
        $newSale->is_delivery       = data_get($saleData, 'is_delivery', false);
        $newSale->remark            = data_get($saleData, 'remark');
        $newSale->total             = 0;
        $newSale->saveOrFail();

        $newSale->items()->initRelation([], 'items');
        $newSale->items()->initRelation([], 'packages');

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

            // save the total sale
            $newSale->total = $newSale->calculateTotal();
            $newSale->saveOrFail();

            // reorder priority
            $this->inventoryService->reOrderPriority($product, $openedBy->branch);
        }

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

            $newSalePackage->items()->initRelation([], 'items');

            foreach ($package->items as $packageItem) {
                $selectedProduct = null;

                // find the selected product or variant
                foreach (data_get($requestedPackage, 'products') as $productId) {
                    if ($selectedProduct === null) {
                        if ((int) $packageItem->product->id === (int) $productId) {
                            $selectedProduct = Product::findOrFail($productId);
                        } elseif ($packageItem->product->variantGroup) {
                            foreach ($packageItem->product->variantGroup->products as $variant) {
                                if ((int) $packageItem->product->id === (int) $variant->id) {
                                    $selectedProduct = $variant;
                                }
                            }
                        }
                    }
                }

                if ($selectedProduct === null) {
                    throw (new ModelNotFoundException())->setModel(Product::class, $packageItem->product->id);
                }

                // get the branch inventories
                $usedInventories = $this->useInventory($selectedProduct, $openedBy->branch, $requestedQuantity);

                foreach ($usedInventories as $usedInventory) {
                    $newSalePackageItem                      = new SalePackageItem();
                    $newSalePackageItem->sale_package_id     = $newSalePackage->id;
                    $newSalePackageItem->product_id          = $selectedProduct->id;
                    $newSalePackageItem->branch_inventory_id = $usedInventory['branchInventory']->id;
                    $newSalePackageItem->quantity            = $usedInventory['availableQuantity'];
                    $newSalePackageItem->original_price      = $selectedProduct->price;
                    $newSalePackageItem->saveOrFail();

                    $newSalePackage->items->push($newSalePackageItem);
                }

                // reorder priority
                $this->inventoryService->reOrderPriority($selectedProduct, $openedBy->branch);
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
        $newPayment->amount         = data_get($paymentData, 'amount');

        if ($newPayment->payment_method === SalePayment::PAYMENT_METHOD_CREDIT_CARD) {
            $newPayment->card_number = data_get($paymentData, 'card_number');
            $newPayment->card_tax    = Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 0);
        }

        $newPayment->saveOrFail();

        if ($newPayment->payment_method === SalePayment::PAYMENT_METHOD_CASH) {
            // @ToDo adjust cash
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

                $this->inventoryService->reOrderPriority($saleItem->product, $sale->creator->branch);
            }
        }

        foreach ($sale->packages as $salePackage) {
            foreach ($salePackage->items as $packageItem) {
                $branchInventory = BranchInventory::findOrFail($packageItem->branch_inventory_id);
                $branchInventory->quantity += $packageItem->quantity;
                $branchInventory->saveOrFail();

                $this->inventoryService->reOrderPriority($packageItem->product, $sale->creator->branch);
            }
        }

        $sale->cancelled_at      = Carbon::now();
        $sale->cancelled_by      = $cancelledBy->id;
        $sale->closed_at         = Carbon::now();
        $sale->closed_by_user_id = $cancelledBy->id;
        $sale->saveOrFail();

        return $sale;
    }
}