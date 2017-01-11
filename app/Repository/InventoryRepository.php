<?php

namespace App\Repository;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
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
     * @return Product[] $products
     */
    public function populateProductStock(\ArrayAccess $products, Branch $inBranch = null)
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
            $product->stock = $stocks->has($product->id)
                ? intval($stocks[$product->id]->total_stock)
                : 0;
        }

        return $products;
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
        return $quantity <= $this->populateProductStock(new Collection([$product]), $inBranch);
    }
}