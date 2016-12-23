<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PersistenceException;
use App\Models\Branch;
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
        $movement                        = new InventoryMovement();
        $movement->from_branch_id        = $fromBranch ? $fromBranch->id : null;
        $movement->to_branch_id          = $toBranch->id;
        $movement->remark                = $remark;
        $movement->movement_effective_at = $effectiveAt ?: Carbon::now();
        $movement->saveOrFail();

        foreach ($items as $item) {
            $productId       = data_get($item, 'product_id');
            $sourceInventory = $fromBranch ? Inventory::findOrFail(data_get($item, 'source_inventory_id')) : null;

            if ($sourceInventory && $sourceInventory->branch_id !== $fromBranch->id) {
                throw new ModelNotFoundException(Inventory::class);
            }

            if ($sourceInventory && $sourceInventory->stock < data_get($item, 'quantity')) {
                throw new InsufficientStockException($sourceInventory);
            }

            if ($sourceInventory) {
                $priority = $sourceInventory->priority;
            } else {
                $priority = Inventory::inBranch($toBranch)->where('product_id', '=', $productId)->max('priority') + 1;
            }

            $movementItem                            = new InventoryMovementItem();
            $movementItem->product_id                = $productId;
            $movementItem->expired_at                = Carbon::createFromFormat('Y-m-d', data_get($item, 'expire_date'));
            $movementItem->expiry_reminder_date      = Carbon::createFromFormat('Y-m-d', data_get($item, 'expiry_reminder_date'));
            $movementItem->cost                      = data_get($item, 'cost');
            $movementItem->quantity                  = data_get($item, 'quantity');
            $movementItem->source_current_stock      = $sourceInventory ? Inventory::inBranch($fromBranch)->where('product_id', '=', $productId)->sum('stock') : null;
            $movementItem->source_inventory_id       = $sourceInventory ? $sourceInventory->id : null;
            $movementItem->destination_current_stock = Inventory::inBranch($toBranch)->where('product_id', '=', $productId)->sum('stock');

            $movementItem = $movement->items()->save($movementItem);

            if (!$movementItem) {
                throw new PersistenceException($movementItem);
            }

            if ($fromBranch) {
                $sourceInventory->stock -= $movementItem->quantity;
                $sourceInventory->saveOrFail();
            }

            $newInventory                       = new Inventory();
            $newInventory->branch_id            = $toBranch->id;
            $newInventory->product_id           = $movementItem->product_id;
            $newInventory->priority             = $priority;
            $newInventory->cost                 = $movementItem->cost;
            $newInventory->stock                = $movementItem->quantity;
            $newInventory->expired_at           = $movementItem->expired_at;
            $newInventory->expiry_reminder_date = $movementItem->expiry_reminder_date;
            $newInventory->saveOrFail();
        }

        return $movement;
    }
}