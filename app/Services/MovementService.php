<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PersistenceException;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class MovementService
 *
 * @package App\Services
 */
class MovementService
{
    private $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a movement
     *
     * @param Branch      $toBranch
     * @param array       $items
     * @param Branch|null $fromBranch
     * @param null        $remark
     * @param null        $effectiveAt
     *
     * @return InventoryMovement
     */
    public function createMovement(Branch $toBranch, array $items, Branch $fromBranch = null, $remark = null, $effectiveAt = null)
    {
        $isImportTransaction = $fromBranch === null;

        // create movement transaction
        $movement                        = new InventoryMovement();
        $movement->from_branch_id        = $fromBranch ? $fromBranch->id : null;
        $movement->to_branch_id          = $toBranch->id;
        $movement->remark                = $remark;
        $movement->movement_effective_at = $effectiveAt ?: Carbon::now();
        $movement->saveOrFail();

        foreach ($items as $item) {
            $productId       = data_get($item, 'product_id');
            $product         = Product::findOrFail($productId);
            $sourceInventory = $isImportTransaction === false
                ? BranchInventory::findOrFail(data_get($item, 'source_inventory_id'))
                : null;

            if ($sourceInventory && $sourceInventory->branch_id !== $fromBranch->id) {
                throw new ModelNotFoundException(BranchInventory::class);
            }

            if ($sourceInventory && $sourceInventory->stock < data_get($item, 'quantity')) {
                throw new InsufficientStockException($sourceInventory->inventory->product, data_get($item, 'quantity'));
            }

            $movementItem                                 = new InventoryMovementItem();
            $movementItem->product_id                     = $product->id;
            $movementItem->product_item_id                = $product->isBulkContainer() ? $product->product_item_id : null;
            $movementItem->product_item_quantity          = $product->isBulkContainer() ? $product->product_item_quantity : 0;
            $movementItem->expired_at                     = $sourceInventory ? $sourceInventory->inventory->expired_at : Carbon::createFromFormat('Y-m-d', data_get($item, 'expire_date'));
            $movementItem->expiry_reminder_date           = $sourceInventory ? $sourceInventory->inventory->expiry_reminder_date : Carbon::createFromFormat('Y-m-d', data_get($item, 'expiry_reminder_date'));
            $movementItem->cost                           = $sourceInventory ? $sourceInventory->inventory->cost : data_get($item, 'cost');
            $movementItem->quantity                       = data_get($item, 'quantity');
            $movementItem->source_current_stock           = $sourceInventory ? BranchInventory::inBranch($fromBranch)->product($product)->sum('stock') : null;
            $movementItem->source_branch_inventory_id     = $sourceInventory ? $sourceInventory->id : null;
            $movementItem->destination_current_stock      = BranchInventory::inBranch($toBranch)->product($product)->sum('stock');
            $movementItem->source_item_current_stock      = $sourceInventory && $product->isBulkContainer() ? BranchInventory::inBranch($fromBranch)->product($product->item)->sum('stock') : null;
            $movementItem->destination_item_current_stock = $product->isBulkContainer() ? BranchInventory::inBranch($toBranch)->product($product->item)->sum('stock') : null;

            $movementItem = $movement->items()->save($movementItem);

            if (!$movementItem) {
                throw new PersistenceException($movementItem);
            }
        }

        if ($isImportTransaction) {
            $this->adjustInventoryImportTransaction($movement);
        } else {
            $this->adjustInventoryMovementTransaction($movement);
        }

        foreach ($movement->items as $movementItem) {
            $this->inventoryService->reOrderPriority($movementItem->product, $toBranch);

            if ($fromBranch) {
                $this->inventoryService->reOrderPriority($movementItem->product, $fromBranch);
            }
        }

        return $movement;
    }

    protected function adjustInventoryMovementTransaction(InventoryMovement $movement)
    {
        /** @var InventoryMovementItem $movementItem */
        foreach ($movement->items as $movementItem) {
            $sourceInventory = BranchInventory::findOrFail($movementItem->source_branch_inventory_id);

            $sourceInventory->stock -= $movementItem->quantity;
            $sourceInventory->saveOrFail();

            $destinationBranchInventory = BranchInventory::firstOrNew([
                'branch_id'    => $movement->to_branch_id,
                'inventory_id' => $sourceInventory->inventory_id
            ]);

            // if it exists, simply adjust the stock, if it doesn't, have it compared with the global priority
            if (!$destinationBranchInventory->exists) {
                $incomingGlobalPriority    = $sourceInventory->inventory->priority;
                $sortedSameProductInBranch = BranchInventory::with('inventory')
                    ->inBranch($movement->to)
                    ->product($movementItem->product)
                    ->orderBy('inventories.priority', 'asc')
                    ->orderBy('branch_inventories.priority', 'asc')
                    ->get();
                $lowerPriorityInBranch     = $sortedSameProductInBranch->filter(function (BranchInventory $branchInventory) use ($incomingGlobalPriority) {
                    return $branchInventory->inventory->priority > $incomingGlobalPriority;
                });

                if ($lowerPriorityInBranch->count() > 0) {
                    $destinationBranchInventory->priority = $lowerPriorityInBranch->first()->priority;

                    foreach ($lowerPriorityInBranch as $branchInventory) {
                        $branchInventory->priority -= 1;
                        $branchInventory->saveOrFail();
                    }
                } else {
                    $destinationBranchInventory->priority = ($sortedSameProductInBranch->max('priority') ?: 0) + 1;
                }
            }

            $destinationBranchInventory->stock += $movementItem->quantity;
            $destinationBranchInventory->saveOrFail();

            $this->inventoryService->adjustContainerStock($sourceInventory, $movementItem->quantity, InventoryService::MOVEMENT_TYPE_SUBTRACTION);
            $this->inventoryService->adjustContainerStock($destinationBranchInventory, $movementItem->quantity, InventoryService::MOVEMENT_TYPE_ADDITION);
        }
    }

    protected function adjustInventoryImportTransaction(InventoryMovement $movement)
    {
        /** @var InventoryMovementItem $movementItem */
        foreach ($movement->items as $movementItem) {
            $product = $movementItem->product;

            $newInventory                       = new Inventory();
            $newInventory->product_id           = $movementItem->product_id;
            $newInventory->priority             = $this->getNewInventoryPriority($product);
            $newInventory->cost                 = $movementItem->cost;
            $newInventory->expired_at           = $movementItem->expired_at;
            $newInventory->expiry_reminder_date = $movementItem->expiry_reminder_date;
            $newInventory->initial_movement_id  = $movement->id;
            $newInventory->saveOrFail();

            $newBranchInventory                   = new BranchInventory();
            $newBranchInventory->branch_id        = $movement->to_branch_id;
            $newBranchInventory->inventory_id     = $newInventory->id;
            $newBranchInventory->priority         = $this->getNewBranchInventoryPriority($product, $movement->to);
            $newBranchInventory->stock            = $movementItem->quantity;
            $newBranchInventory->content_quantity = $product->isBulkContainer() ? $product->product_item_quantity : 1;
            $newBranchInventory->saveOrFail();

            if ($product->isBulkContainer()) {
                $productItem = Product::findOrFail($product->product_item_id);

                $newInventoryItem                       = new Inventory();
                $newInventoryItem->product_id           = $productItem->id;
                $newInventoryItem->priority             = $this->getNewInventoryPriority($productItem);
                $newInventoryItem->cost                 = $movementItem->cost / $product->product_item_quantity;
                $newInventoryItem->expired_at           = $movementItem->expired_at;
                $newInventoryItem->expiry_reminder_date = $movementItem->expiry_reminder_date;
                $newInventoryItem->initial_movement_id  = $movement->id;
                $newInventoryItem->saveOrFail();

                $newBranchInventoryItem                   = new BranchInventory();
                $newBranchInventoryItem->branch_id        = $movement->to_branch_id;
                $newBranchInventoryItem->inventory_id     = $newInventoryItem->id;
                $newBranchInventoryItem->priority         = $this->getNewBranchInventoryPriority($productItem, $movement->to);
                $newBranchInventoryItem->stock            = $movementItem->quantity * $product->product_item_quantity;
                $newBranchInventoryItem->container_id     = $newBranchInventory->id;
                $newBranchInventoryItem->content_quantity = 1;
                $newBranchInventoryItem->saveOrFail();
            }
        }
    }

    public function getNewInventoryPriority(Product $product)
    {
        $priority = Inventory::where('product_id', '=', $product->id)->max('priority');
        $priority = $priority ? $priority + 1 : 1;

        return $priority;
    }

    public function getNewBranchInventoryPriority(Product $product, Branch $branch)
    {
        $priority = BranchInventory::inBranch($branch)->product($product)->max('branch_inventories.priority');
        $priority = $priority ? $priority + 1 : 1;

        return $priority;
    }
}