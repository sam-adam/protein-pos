<?php

namespace App\Repository;

use App\Models\Package;
use App\Models\PackageProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PackageRepository
 *
 * @package App\Repository
 */
class PackageRepository
{
    /**
     * Find all available packages for a list of products
     *
     * @param Product[] $products
     *
     * @return Collection
     */
    public function findAvailablePackages(\ArrayAccess $products)
    {
        $foundPackages = [];
        $productIds    = [];

        foreach ($products as $product) {
            $productIds[] = $product->id;
        }

        $packages = Package::with('items')
            ->select('packages.*')
            ->join('package_products', 'packages.id', '=', 'package_products.package_id')
            ->whereIn('package_products.product_id', $productIds)
            ->groupBy('packages.id')
            ->get();

        foreach ($products as $product) {
            $foundPackages[$product->id] = $packages->filter(function (Package $package) use ($product) {
                return $package->items->filter(function (PackageProduct $packageProduct) use ($product) {
                    return (int) $packageProduct->product_id === (int) $product->id;
                })->count() > 0;
            });
        }

        return new Collection($foundPackages);
    }
}