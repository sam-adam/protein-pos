<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;

/**
 * Class InventoryService
 *
 * @package App\Services
 */
class InventoryService
{
    const MOVEMENT_TYPE_ADDITION    = 'add';
    const MOVEMENT_TYPE_SUBTRACTION = 'sub';

    /**
     * Reorder the priority of a product
     *
     * @param Product $product
     * @param Branch  $inBranch
     */
    public function reOrderPriority(Product $product, Branch $inBranch)
    {
        $currentPriority   = 1;
        $branchInventories = BranchInventory::product($product)
            ->inBranch($inBranch)
            ->notEmpty()
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($branchInventories as $branchInventory) {
            $branchInventory->priority = $currentPriority;
            $branchInventory->saveOrFail();

            $currentPriority++;
        }
    }

    /**
     * Adjust a container stock based on quantity
     *
     * @param BranchInventory $branchInventory
     */
    public function adjustContainerStock(BranchInventory $branchInventory, $containerMovementQuantity = 0, $movementType = self::MOVEMENT_TYPE_ADDITION)
    {
        $container = $branchInventory->container;

        if ($container && $container->inventory->product) { // inventory belongs to a container, and product is still valid
            $container->stock = round(floor($branchInventory->stock / $container->content_quantity));
            $container->saveOrFail();

            $this->reOrderPriority($branchInventory->inventory->product, $branchInventory->branch);
            $this->reOrderPriority($container->inventory->product, $branchInventory->branch);
        } elseif ($item = BranchInventory::where('container_id', '=', $branchInventory->id)->first()) { // inventory is a container
            if ($movementType === self::MOVEMENT_TYPE_ADDITION) {
                $item->stock += $containerMovementQuantity * $branchInventory->content_quantity;
            } else {
                $item->stock -= $containerMovementQuantity * $branchInventory->content_quantity;
            }

            $item->saveOrFail();

            $this->reOrderPriority($branchInventory->inventory->product, $branchInventory->branch);
            $this->reOrderPriority($item->inventory->product, $branchInventory->branch);
        }
    }
}