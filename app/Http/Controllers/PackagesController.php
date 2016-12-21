<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackage;
use App\Models\Package;
use App\Models\PackageProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Class PackagesController
 *
 * @package App\Http\Controllers
 */
class PackagesController extends AuthenticatedController
{
    public function index()
    {
        return view('packages.index', ['packages' => Package::with('items.product')->paginate()]);
    }

    public function show()
    {
        return view('packages.show');
    }

    public function create()
    {
        return view('packages.create', [
            'products' => Product::select('id', 'name', 'price')->where('is_service', '=', false)->get()
        ]);
    }

    public function store(StorePackage $request)
    {
        DB::transaction(function () use ($request) {
            $newPackage                  = new Package();
            $newPackage->name            = $request->get('name');
            $newPackage->price           = $request->get('price');
            $newPackage->is_customizable = $request->get('is_customizable') ?: false;
            $newPackage->saveOrFail();

            foreach ($request->get('products') as $productData) {
                $newPackageItem             = new PackageProduct();
                $newPackageItem->product_id = data_get($productData, 'id');
                $newPackageItem->quantity   = data_get($productData, 'quantity');

                $newPackage->items()->save($newPackageItem);
            }

            return $newPackage;
        });

        return redirect(route('packages.index'))->with('flashes.success', 'Package added');
    }

    public function edit($packageId)
    {
        $package = Package::find($packageId);

        if (!$package) {
            return redirect()->back()->with('flashes.error', 'Package not found');
        }

        return view('packages.edit', [
            'package'      => $package,
            'packageItems' => $package->items->keyBy('product_id')->map(function (PackageProduct $item) { return ['id' => $item->product_id, 'quantity' => $item->quantity]; }),
            'products'     => Product::select('id', 'name', 'price')->where('is_service', '=', false)->get()
        ]);
    }

    public function update(StorePackage $request, $packageId)
    {
        $package = Package::find($packageId);

        if (!$package) {
            return redirect()->back()->with('flashes.error', 'Package not found');
        }

        DB::transaction(function () use ($package, $request) {
            $package->name            = $request->get('name');
            $package->price           = $request->get('price');
            $package->is_customizable = $request->get('is_customizable') ?: false;
            $package->saveOrFail();

            PackageProduct::where('package_id', '=', $package->id)->delete();

            foreach ($request->get('products') as $productData) {
                $newPackageItem             = new PackageProduct();
                $newPackageItem->product_id = data_get($productData, 'id');
                $newPackageItem->quantity   = data_get($productData, 'quantity');

                $package->items()->save($newPackageItem);
            }

            return $package;
        });

        return redirect(route('packages.index'))->with('flashes.success', 'Package edited');
    }

    public function destroy($packageId)
    {
        $package = Package::find($packageId);

        if (!$package) {
            return redirect()->back()->with('flashes.error', 'Package not found');
        }

        $package->delete();

        return redirect(route('packages.index'))->with('flashes.success', 'Package deleted');
    }
}