<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PersistenceException;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class MovementService
 *
 * @package App\Services
 */
class MovementService
{
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
                throw new InsufficientStockException($sourceInventory);
            }

            $movementItem                             = new InventoryMovementItem();
            $movementItem->product_id                 = $product->id;
            $movementItem->expired_at                 = $sourceInventory ? $sourceInventory->inventory->expired_at : Carbon::createFromFormat('Y-m-d', data_get($item, 'expire_date'));
            $movementItem->expiry_reminder_date       = $sourceInventory ? $sourceInventory->inventory->expiry_reminder_date : Carbon::createFromFormat('Y-m-d', data_get($item, 'expiry_reminder_date'));
            $movementItem->cost                       = $sourceInventory ? $sourceInventory->inventory->cost : data_get($item, 'cost');
            $movementItem->quantity                   = data_get($item, 'quantity');
            $movementItem->source_current_stock       = $sourceInventory ? BranchInventory::inBranch($fromBranch)->product($product)->sum('stock') : null;
            $movementItem->source_branch_inventory_id = $sourceInventory ? $sourceInventory->id : null;
            $movementItem->destination_current_stock  = BranchInventory::inBranch($toBranch)->product($product)->sum('stock');

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
            $destinationBranchInventory->stock += $movementItem->quantity;
            $destinationBranchInventory->saveOrFail();
        }
    }

    protected function adjustInventoryImportTransaction(InventoryMovement $movement)
    {
        /** @var InventoryMovementItem $movementItem */
        foreach ($movement->items as $movementItem) {
            $currentPriority = Inventory::where('product_id', '=', $movementItem->product_id)->max('priority');
            $priority        = $currentPriority ? $currentPriority + 1 : 1;

            $newInventory                       = new Inventory();
            $newInventory->product_id           = $movementItem->product_id;
            $newInventory->priority             = $priority;
            $newInventory->cost                 = $movementItem->cost;
            $newInventory->expired_at           = $movementItem->expired_at;
            $newInventory->expiry_reminder_date = $movementItem->expiry_reminder_date;
            $newInventory->initial_movement_id  = $movement->id;
            $newInventory->saveOrFail();

            $newBranchInventory               = new BranchInventory();
            $newBranchInventory->branch_id    = $movement->to_branch_id;
            $newBranchInventory->inventory_id = $newInventory->id;
            $newBranchInventory->stock        = $movementItem->quantity;
            $newBranchInventory->saveOrFail();
        }
    }
}