<?php

namespace App\Http\Controllers;

use App\DataObjects\Decorators\Package\WithItemsDecorator;
use App\Http\Requests\StorePackage;
use App\Models\Package;
use App\Models\PackageProduct;
use App\Models\PackageVariant;
use App\Models\Product;
use App\Models\ProductVariantGroup;
use App\Repository\InventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class PackagesController
 *
 * @package App\Http\Controllers
 */
class PackagesController extends AuthenticatedController
{
    protected $inventoryRepo;

    public function __construct(InventoryRepository $inventoryRepo)
    {
        parent::__construct();

        $this->inventoryRepo = $inventoryRepo;
    }

    public function index()
    {
        return view('packages.index', ['packages' => Package::with('items.product')->paginate()]);
    }

    public function show(Request $request, $packageId)
    {
        /** @var Package $package */
        $package = Package::with('items.product.variantGroup.products')->find($packageId);

        if (!$package) {
            return redirect()->back()->with('flashes.danger', 'Package not found');
        }

        $stocks     = $this->inventoryRepo->getStocksByPackage($package, Auth::user()->branch);
        $dataObject = new \App\DataObjects\Package($package);
        $dataObject->addDecorator(new WithItemsDecorator($package, $stocks));

        return view('packages.show', [
            'package'     => $package,
            'stocks'      => $stocks,
            'packageJson' => json_encode($dataObject),
            'variants'    => ProductVariantGroup::all(),
            'intent'      => $request->get('intent')
        ]);
    }

    public function create()
    {
        return view('packages.create', [
            'products' => Product::select('id', 'name', 'price')->where('is_service', '=', false)->get(),
            'variants' => ProductVariantGroup::all(),
        ]);
    }

    public function store(StorePackage $request)
    {
        DB::transaction(function () use ($request) {
            $newPackage                  = new Package();
            $newPackage->name            = $request->get('name');
            $newPackage->code            = $request->get('code');
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
            'package'         => $package,
            'packageItems'    => $package->items->keyBy('product_id')->map(function (PackageProduct $item) { return ['id' => $item->product_id, 'quantity' => $item->quantity]; }),
            'products'        => Product::select('id', 'name', 'price')->where('is_service', '=', false)->get(),
            'packageVariants' => $package->variants->keyBy('product_variant_group_id')->map(function (PackageVariant $item) { return ['id' => $item->product_variant_group_id]; }),
            'variants'        => ProductVariantGroup::all()
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
            $package->code            = $request->get('code');
            $package->price           = $request->get('price');
            $package->is_customizable = $request->get('is_customizable') ?: false;
            $package->saveOrFail();

            PackageProduct::where('package_id', '=', $package->id)->delete();
            PackageVariant::where('package_id', '=', $package->id)->delete();

            foreach ($request->get('products', []) as $productData) {
                $newPackageItem             = new PackageProduct();
                $newPackageItem->product_id = data_get($productData, 'id');
                $newPackageItem->quantity   = data_get($productData, 'quantity');

                $package->items()->save($newPackageItem);
            }

            foreach ($request->get('variants', []) as $variantData) {
                $newPackageVariant                           = new PackageVariant();
                $newPackageVariant->product_variant_group_id = data_get($variantData, 'id');

                $package->variants()->save($newPackageVariant);
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

    public function xhrInfo($packageId)
    {
        /** @var Package $package */
        $package = Package::with('items.product')->find($packageId);

        if (!$package) {
            return response()->json();
        }

        $products = [];

        foreach ($package->items as $item) {
            if (!isset($products[$item->product->id])) {
                $products[$item->product->id] = $item->product;

                if ($item->product->variantGroup) {
                    foreach ($item->product->variantGroup->products as $variant) {
                        if (!isset($products[$variant->id])) {
                            $products[$variant->id] = $variant;
                        }
                    }
                }
            }
        }

        $stocks = $this->inventoryRepo->getProductStocks(new Collection($products), Auth::user()->branch);

        $dataObject = new \App\DataObjects\Package($package);
        $dataObject->addDecorator(new WithItemsDecorator($package, $stocks));

        return response()->json($dataObject);
    }
}