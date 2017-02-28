<?php

namespace App\Repository;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\InventoryRemoval;
use App\Models\Package;
use App\Models\Product;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class InventoryRepository
 *
 * @package App\Repository
 */
class InventoryRepository
{
    /**
     * Populate the stocks of an array of products
     *
     * @param Product[] $products
     * @param Branch    $inBranch
     *
     * @return Collection
     */
    public function getProductStocks(\ArrayAccess $products, Branch $inBranch = null)
    {
        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product->id;
        }

        $stocksQuery = BranchInventory::select(
                'branch_inventories.id',
                'inventories.product_id',
                DB::raw('SUM(branch_inventories.stock) AS total_stock')
            )
            ->join('inventories', 'branch_inventories.inventory_id', '=', 'inventories.id')
            ->whereIn('inventories.product_id', $productIds)
            ->groupBy('inventories.product_id');

        if ($inBranch) {
            $stocksQuery = $stocksQuery->where('branch_inventories.branch_id', '=', $inBranch->id);
        }

        $stocks = $stocksQuery->get()->keyBy('product_id');

        foreach ($products as $product) {
            $stocks[$product->id] = $stocks->has($product->id)
                ? intval($stocks[$product->id]->total_stock)
                : 0;
        }

        return new Collection($stocks);
    }

    /**
     * Get stocks of products in a package
     *
     * @param Package     $package
     * @param Branch|null $inBranch
     *
     * @return Collection
     */
    public function getStocksByPackage(Package $package, Branch $inBranch = null)
    {
        $products = [];

        foreach ($package->items as $item) {
            if (!isset($products[$item->product->id])) {
                $products[$item->product->id] = $item->product;
            }
        }

        foreach ($package->variants as $packageVariant) {
            foreach ($packageVariant->variant->items as $variantItem) {
                if (!isset($products[$variantItem->product->id])) {
                    $products[$variantItem->product->id] = $variantItem->product;
                }
            }
        }

        return $this->getProductStocks(new Collection($products), $inBranch);
    }

    /**
     * Check if there are a sufficient stock for a product
     *
     * @param Product     $product
     * @param int         $quantity
     * @param Branch|null $inBranch
     *
     * @return bool
     */
    public function checkIfStockSufficient(Product $product, $quantity, Branch $inBranch = null)
    {
        $stocks = $this->getProductStocks(new Collection([$product]), $inBranch);

        return $stocks->has($product->id)
            && $quantity <= $stocks->get($product->id);
    }

    /**
     * Get the movements happened to a product
     *
     * @param Product $product
     * @param Branch  $branch
     * @param Carbon  $from
     * @param Carbon  $to
     *
     * @return array
     */
    public function getMovements(Product $product, Branch $branch, Carbon $from = null, Carbon $to = null)
    {
        $movementLabels = [];
        $movementsQuery = InventoryMovement::with('items')
            ->branch($branch)
            ->select('inventory_movements.*')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->where(function ($query) use ($product) {
                return $query->where('inventory_movement_items.product_id', '=', $product->id)
                    ->orWhere('inventory_movement_items.product_item_id', '=', $product->id);
            })
            ->groupBy('inventory_movements.id')
            ->orderBy('inventory_movements.created_at', 'desc');
        $removalsQuery  = InventoryRemoval::with('branchInventory.branch')
            ->select('inventory_removals.*')
            ->join('branch_inventories', 'branch_inventories.id', '=', 'inventory_removals.branch_inventory_id')
            ->join('inventories', 'branch_inventories.inventory_id', '=', 'inventories.id')
            ->where(function ($query) use ($product) {
                return $query->where('inventory_removals.product_id', '=', $product->id)
                    ->orWhere('inventory_removals.product_item_id', '=', $product->id);
            })
            ->orderBy('inventory_removals.created_at', 'desc');

        if ($from && $to) {
            $movements = $movementsQuery->whereBetween('inventory_movements.movement_effective_at', [$from, $to])->get();
            $removals  = $removalsQuery->whereBetween('inventory_removals.created_at', [$from, $to])->get();
        } else {
            $movements = $movementsQuery->get();
            $removals  = $removalsQuery->get();
        }

        foreach ($movements as $movement) {
            foreach ($movement->items as $movementItem) {
                $isContainerMatched = $movementItem->product_id == $product->id;
                $isItemMatched      = $movementItem->product_item_id == $product->id;

                if (!$isContainerMatched && !$isItemMatched) {
                    continue;
                }

                if ($isContainerMatched && $movement->from_branch_id) {
                    $movementLabel = 'Inventory transfer '.($movement->from_branch_id == $branch->id ? 'to ' : 'from ').$movement->to->name;
                } elseif ($isItemMatched && $movement->from_branch_id) {
                    $movementLabel = 'Part of container import ('.$movementItem->quantity.' x '.$movementItem->product_item_quantity.' pcs) '.($movement->from_branch_id == $branch->id ? 'to ' : 'from ').$movement->to->name;
                } elseif ($isContainerMatched) {
                    $movementLabel = 'Inventory import at '.$movement->to->name;
                } else {
                    $movementLabel = 'Part of container import ('.$movementItem->quantity.' x '.$movementItem->product_item_quantity.' pcs) at '.$movement->to->name;
                }

                $movementLabels[] = [
                    'id'                    => $movement->id,
                    'label'                 => $movementLabel,
                    'sourceBranch'          => $movement->from,
                    'targetBranch'          => $movement->to,
                    'costPerItem'           => $movementItem->cost,
                    'isContainer'           => $isContainerMatched,
                    'container'             => $isContainerMatched ? null : $movementItem->product,
                    'containerQuantity'     => $isContainerMatched ? 0 : $movementItem->quantity,
                    'containerItemQuantity' => $isContainerMatched ? 0 : $movementItem->product_item_quantity,
                    'quantity'              => $isContainerMatched ? $movementItem->quantity : $movementItem->quantity * $movementItem->product_item_quantity,
                    'date'                  => $movement->created_at,
                    'dateString'            => $movement->movement_effective_at->toDayDateTimeString(),
                    'actor'                 => $movement->creator->name,
                    'remark'                => $movement->remark,
                    'direction'             => InventoryService::MOVEMENT_TYPE_ADDITION
                ];
            }
        }

        foreach ($removals as $inventoryRemoval) {
            $isContainerMatched = $inventoryRemoval->product_id == $product->id;

            if ($isContainerMatched) {
                $removalLabel = 'Removed from '.$inventoryRemoval->branchInventory->branch->name;
            } else {
                $removalLabel = 'Container removed ('.$inventoryRemoval->quantity.' x '.$inventoryRemoval->product_item_quantity.' pcs) from '.$inventoryRemoval->branchInventory->branch->name;
            }

            $movementLabels[] = [
                'id'                    => $inventoryRemoval->id,
                'label'                 => $removalLabel,
                'sourceBranch'          => $inventoryRemoval->branchInventory->branch,
                'targetBranch'          => null,
                'costPerItem'           => $inventoryRemoval->branchInventory->inventory->cost,
                'isContainer'           => $isContainerMatched,
                'container'             => $isContainerMatched ? null : $inventoryRemoval->product,
                'containerQuantity'     => $isContainerMatched ? 0 : $inventoryRemoval->quantity,
                'containerItemQuantity' => $isContainerMatched ? 0 : $inventoryRemoval->product_item_quantity,
                'quantity'              => -($isContainerMatched ? $inventoryRemoval->quantity : $inventoryRemoval->quantity * $inventoryRemoval->product_item_quantity),
                'date'                  => $inventoryRemoval->created_at,
                'dateString'            => $inventoryRemoval->created_at->toDayDateTimeString(),
                'actor'                 => $inventoryRemoval->creator->name,
                'remark'                => $inventoryRemoval->remark,
                'direction'             => InventoryService::MOVEMENT_TYPE_SUBTRACTION
            ];
        }

        uasort($movementLabels, function ($first, $second) {
            return $first['date']->lt($second['date']);
        });

        return $movementLabels;
    }
}