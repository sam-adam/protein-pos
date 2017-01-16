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
}