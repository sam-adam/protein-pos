<?php

namespace App\Http\Controllers;

use App\DataObjects\CollectionDataObject;
use App\DataObjects\Decorators\Package as PackageDecorators;
use App\DataObjects\Decorators\Product as ProductDecorators;
use App\DataObjects\ProductVariantGroup;
use App\Http\Requests\MoveInventoryToOtherBranch;
use App\Http\Requests\RemoveInventory;
use App\Http\Requests\StoreProduct;
use App\Http\Requests\AddProductMovement;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryRemoval;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Repository as Repositories;
use App\Services\InventoryService;
use App\Services\MovementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

/**
 * Class ProductsController
 *
 * @package App\Http\Controllers
 */
class ProductsController extends AuthenticatedController
{
    protected $movementService;
    protected $inventoryService;
    protected $inventoryRepo;
    protected $packageRepo;
    protected $productRepo;
    protected $variantRepo;

    public function __construct(
        MovementService $movementService,
        InventoryService $inventoryService,
        Repositories\InventoryRepository $inventoryRepo,
        Repositories\PackageRepository $packageRepo,
        Repositories\ProductRepository $productRepo,
        Repositories\ProductVariantRepository $variantRepo
    ) {
        parent::__construct();

        $this->movementService  = $movementService;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepo    = $inventoryRepo;
        $this->packageRepo      = $packageRepo;
        $this->productRepo      = $productRepo;
        $this->variantRepo      = $variantRepo;
    }

    public function index(Request $request)
    {
        $query         = $request->get('query');
        $perPage       = 24;
        $products      = null;
        $productsJson  = new Collection();
        $productsQuery = Product::with('category', 'brand', 'item')->select('products.*');
        $categoryTree  = [];
        $categories    = ProductCategory::with('parent')
            ->orderBy('parent_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($categories as $category) {
            if ($category->isRoot()) {
                $category->displayName = 'All '.$category->name;
            } else {
                $category->displayName = $category->parent->name.' - '.$category->name;
            }
        }

        foreach ($categories as $category) {
            if ($category->isRoot()) {
                $categoryTree[$category->id] = [
                    'id'       => $category->id,
                    'name'     => $category->name,
                    'children' => [
                        ['id' => $category->id, 'name' => $category->displayName]
                    ]
                ];
            } else {
                $categoryTree[$category->parent_id]['children'][] = [
                    'id'   => $category->id,
                    'name' => $category->displayName
                ];
            }
        }

        if ($query) {
            $productByBarcode = Product::where('barcode', 'like', "%{$query}%")->paginate();

            if ($productByBarcode->count() === 0) {
                $productsQuery = $productsQuery->where(function ($subWhere) use ($query) {
                    return $subWhere->where('products.name', 'LIKE', "%{$query}%")
                        ->orWhere('products.code', 'LIKE', "%{$query}%");
                });
            } else {
                $products = $productByBarcode;
            }
        }

        if (!$products && ($categoryId = $request->get('category'))) {
            if ($categoryId === 'uncategorized') {
                $productsQuery = $productsQuery->whereNull('products.product_category_id');
            } else {
                $productsQuery = $productsQuery->leftJoin('product_categories AS child', function (JoinClause $query) use ($categoryId) {
                    return $query->on('child.id', '=', 'products.product_category_id')
                        ->where('child.parent_id', '=', $categoryId);
                });
                $productsQuery = $productsQuery->where(function ($subWhere) use ($categoryId) {
                    return $subWhere->where('products.product_category_id', '=', $categoryId)
                        ->orWhereNotNull('child.id');
                });
            }
        }

        if (!$products && ($brandId = $request->get('brand'))) {
            if ($brandId === 'unbranded') {
                $productsQuery = $productsQuery->whereNull('products.brand_id');
            } else {
                $productsQuery = $productsQuery->where('products.brand_id', '=', $brandId);
            }
        }

        if (!$products) {
            $products = $productsQuery->paginate($perPage);
        }

        $stocks   = $this->inventoryRepo->getProductStocks($products, Auth::user()->branch);
        $packages = $this->packageRepo->findAvailablePackages($products);

        foreach ($products as $product) {
            $product->stock       = BranchInventory::product($product)->sum('stock');
            $product->branchStock = BranchInventory::inBranch(Auth::user()->branch)
                ->product($product)
                ->sum('stock');

            $dataObject = new \App\DataObjects\Product($product);
            $dataObject->addDecorator(new ProductDecorators\BulkContainerDecorator($product));
            $dataObject->addDecorator(new ProductDecorators\StockDecorator($product, $stocks->get($product->id)));
            $dataObject->addDecorator(new ProductDecorators\PackageDecorator($product, $packages->get($product->id)));

            $productsJson[$product->id] = $dataObject;
        }

        Session::put('last_product_page', $request->fullUrl());

        return view('products.index', [
            'products'     => $products->appends(Input::except('page')),
            'productsJson' => $productsJson,
            'categories'   => $categories,
            'categoryTree' => $categoryTree,
            'brands'       => Brand::orderBy('name', 'asc')->get(),
            'showMode'     => count($request->except(['intent', 'external'])) === 0 ? 'category' : 'product',
            'intent'       => $request->get('intent', 'display')
        ]);
    }

    public function create()
    {
        $this->authorize('update', Product::class);

        return view('products.create', [
            'brands'     => Brand::orderBy('name', 'asc')->get(),
            'products'   => Product::nonContainer()->orderBy('name', 'asc')->get(),
            'categories' => ProductCategory::with('parent')
                ->select('product_categories.*')
                ->join('product_categories as parent', 'parent.id', 'product_categories.parent_id')
                ->orderBy('parent.name', 'asc')
                ->orderBy('product_categories.name', 'asc')
                ->get()
        ]);
    }

    public function store(StoreProduct $request)
    {
        $this->authorize('update', Product::class);

        $newProduct                        = new Product();
        $newProduct->is_service            = $request->get('is_service') ?: false;
        $newProduct->product_item_id       = $request->get('product_item_id') ?: null;
        $newProduct->product_item_quantity = $newProduct->isBulkContainer() ? $request->get('product_item_quantity') : 1;
        $newProduct->name                  = $request->get('name');
        $newProduct->price                 = $request->get('price') ?: 0;
        $newProduct->code                  = $request->get('code');
        $newProduct->barcode               = $request->get('barcode');

        if ($newProduct->isBulkContainer()) {
            $productItem = Product::find($request->get('product_item_id'));

            if (!$productItem) {
                return redirect()->back()->with('flashes.error', 'Product item not found');
            }

            $brand    = $productItem->brand;
            $category = $productItem->category;
        } else {
            $brand    = Brand::find($request->get('brand'));
            $category = ProductCategory::find($request->get('category'));
        }

        $newProduct->brand_id            = $brand ? $brand->id : null;
        $newProduct->product_category_id = $category ? $category->id : null;
        $newProduct->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product added');
    }

    public function show($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect(route('products.index'))->with('flashes.error', 'Product not found');
        }

        $branches    = Branch::licensed()->get()->keyBy('id');
        $inventories = Inventory::with('branchItems')
            ->where('product_id', '=', $product->id)
            ->orderBy('priority', 'asc')
            ->get()
            ->map(function (Inventory $inventory) {
                $inventory->stock = $inventory->branchItems->sum('stock');

                return $inventory;
            });

        foreach ($branches as $branch) {
            $branch->branchInventories        = $inventories->map(function (Inventory $inventory) use ($branch) {
                $inventory              = clone $inventory;
                $inventory->branchItems = $inventory->branchItems->filter(function (BranchInventory $branchInventory) use ($branch) {
                    return $branchInventory->branch_id == $branch->id;
                });
                $inventory->stock       = $inventory->branchItems->sum('stock');

                return $inventory;
            });
            $branch->expiredBranchInventories = $branch->branchInventories->filter(function (Inventory $inventory) { return $inventory->isExpired() && $inventory->stock > 0; });
            $branch->closestExpiredInventory  = $branch->branchInventories->filter(function (Inventory $inventory) { return $inventory->isExpired() === false && $inventory->stock > 0; })->first();
        }

        $expiredInventories   = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
        $closestExpired       = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired() === false; })->first();
        $branchInventoryArray = [];

        foreach ($branches as $branch) {
            $branchInventoryArray[] = [
                'id'          => $branch->id,
                'name'        => $branch->name,
                'inventories' => $branch->branchInventories->map(function (Inventory $inventory) {
                    $inventoryArr = [
                        'id'    => $inventory->id,
                        'items' => $inventory->branchItems->filter(function (BranchInventory $branchInventory) {
                            return $branchInventory->stock > 0;
                        })
                            ->map(function (BranchInventory $branchInventory) {
                                return [
                                    'id'       => $branchInventory->id,
                                    'stock'    => $branchInventory->stock,
                                    'priority' => $branchInventory->priority
                                ];
                            })->toArray()
                    ];

                    return $inventoryArr;
                })->toArray()
            ];
        }

        return view('products.show', [
            'now'                       => Carbon::now(),
            'product'                   => $product,
            'branches'                  => $branches,
            'inventories'               => $inventories,
            'branchInventoryArray'      => $branchInventoryArray,
            'allowedMovementQuantity'   => $branches[Auth::user()->branch_id]->branchInventories->sum('stock'),
            'expiredInventories'        => $expiredInventories,
            'closestExpired'            => $closestExpired,
            'movements'                 => $this->inventoryRepo->getMovements($product, $branch),
            'currentBranch'             => $branches[Auth::user()->branch_id],
            'otherBranches'             => $branches->except(Auth::user()->branch_id),
            'defaultMovementDate'       => Carbon::now(),
            'defaultExpiredDate'        => \Carbon\Carbon::now()->addMonth(1),
            'defaultExpiryReminderDate' => \Carbon\Carbon::now()->addMonth(1)->subWeek(1)
        ]);
    }

    public function edit($productId)
    {
        $this->authorize('update', Product::class);

        $product = Product::find($productId);

        if (!$product) {
            return redirect(route('products.show', $productId))->with('flashes.error', 'Product not found');
        }

        return view('products.edit', [
            'product'    => $product,
            'products'   => Product::nonContainer()->orderBy('name', 'asc')->get(),
            'categories' => ProductCategory::with('parent')
                ->select('product_categories.*')
                ->join('product_categories as parent', 'parent.id', 'product_categories.parent_id')
                ->orderBy('parent.name', 'asc')
                ->orderBy('product_categories.name', 'asc')
                ->get(),
            'brands'     => Brand::orderBy('name', 'asc')->get()
        ]);
    }

    public function update(StoreProduct $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($product, $request) {
            $product->is_service            = $request->get('is_service') ?: false;
            $product->product_item_id       = $request->get('product_item_id') ?: null;
            $product->product_item_quantity = $product->isBulkContainer() ? $request->get('product_item_quantity') : 1;
            $product->name                  = $request->get('name');
            $product->price                 = $request->get('price') ?: 0;
            $product->code                  = $request->get('code');
            $product->barcode               = $request->get('barcode');

            if ($product->isBulkContainer()) {
                $productItem = Product::find($request->get('product_item_id'));

                if (!$productItem) {
                    return redirect()->back()->with('flashes.error', 'Product item not found');
                }

                $brand    = $productItem->brand;
                $category = $productItem->category;
            } else {
                $brand    = Brand::find($request->get('brand'));
                $category = ProductCategory::find($request->get('category'));
            }

            $product->brand_id            = $brand ? $brand->id : null;
            $product->product_category_id = $category ? $category->id : null;
            $product->is_service          = $request->get('is_service') ?: false;
            $product->saveOrFail();
        });

        return redirect(route('products.show', $productId))->with('flashes.success', 'Product edited');
    }

    public function destroy($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        $product->delete();

        return redirect(route('products.index'))->with('flashes.success', 'Product deleted');
    }

    public function addInventory(AddProductMovement $request, $productId)
    {
        $this->authorize('update', Product::class);

        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($request, $productId) {
            $movementItem               = $request->all();
            $movementItem['product_id'] = $productId;

            return $this->movementService->createMovement(
                Auth::user()->branch,
                [$movementItem],
                null,
                $request->get('remark'),
                $request->get('movement_effective_at')
            );
        });

        return redirect()->back()->with('flashes.success', 'Movement created');
    }

    public function moveInventory(MoveInventoryToOtherBranch $request, $productId)
    {
        $this->authorize('update', Product::class);

        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($request, $product) {
            return $this->movementService->createMovement(
                Branch::find($request->get('branch_id')),
                [[
                    'product_id'          => $product->id,
                    'source_inventory_id' => $request->get('inventory_id'),
                    'quantity'            => $request->get('quantity')
                ]],
                Auth::user()->branch,
                $request->get('remark')
            );
        });

        return redirect()->back()->with('flashes.success', 'Inventory moved');
    }

    public function removeInventory(RemoveInventory $request, $productId)
    {
        $this->authorize('update', Product::class);

        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($request, $product) {
            $branchInventory = BranchInventory::find($request->get('branch_inventory_id'));

            if (!$branchInventory) {
                throw new ModelNotFoundException(BranchInventory::class);
            }

            $newRemoval                                  = new InventoryRemoval();
            $newRemoval->product_id                      = $product->id;
            $newRemoval->product_item_id                 = $product->isBulkContainer() ? $product->product_item_id : null;
            $newRemoval->product_item_quantity           = $product->isBulkContainer() ? $product->product_item_quantity : 0;
            $newRemoval->product_pre_adjusted_stock      = BranchInventory::inBranch($branchInventory->branch)->product($product)->sum('stock');
            $newRemoval->product_item_pre_adjusted_stock = $product->isBulkContainer() ? BranchInventory::inBranch($branchInventory->branch)->product($product->item)->sum('stock') : null;
            $newRemoval->branch_inventory_id             = $branchInventory->id;
            $newRemoval->quantity                        = $request->get('quantity');
            $newRemoval->pre_adjusted_stock              = $branchInventory->stock;
            $newRemoval->remark                          = $request->get('remark');
            $newRemoval->saveOrFail();

            $branchInventory->stock -= $newRemoval->quantity;
            $branchInventory->saveOrFail();

            $this->inventoryService->adjustContainerStock($branchInventory, $newRemoval->quantity, InventoryService::MOVEMENT_TYPE_SUBTRACTION);
        });

        return redirect()->back()->with('flashes.success', 'Inventory removed');
    }

    public function xhrSearch(Request $request)
    {
        $method         = 'normal';
        $query          = $request->get('query');
        $branch         = Auth::user()->branch;
        $includePackage = $request->get('include-package', false);
        $limit          = $request->get('limit', 5);

        if ($product = $this->productRepo->findByBarcode($query)) {
            $products = new Collection([$product]);
            $method   = 'barcode';
        } else {
            $products = $this->productRepo->findByQuery($query, $limit);
        }

        $collection = new CollectionDataObject();
        $collection->setKey('products');

        $stocks   = $this->inventoryRepo->getProductStocks($products, $branch);
        $packages = $this->packageRepo->findAvailablePackages($products);

        foreach ($products as $product) {
            $dataObject = new \App\DataObjects\Product($product);
            $dataObject->addDecorator(new ProductDecorators\BulkContainerDecorator($product));
            $dataObject->addDecorator(new ProductDecorators\StockDecorator($product, $stocks->get($product->id)));
            $dataObject->addDecorator(new ProductDecorators\PackageDecorator($product, $packages->get($product->id)));

            $collection->add($dataObject);
        }

        if ($includePackage) {
            $packagesResult = [];

            foreach ($this->packageRepo->findByQuery($query, $limit) as $package) {
                $stocksByPackage = $this->inventoryRepo->getStocksByPackage($package);

                $dataObject = new \App\DataObjects\Package($package);
                $dataObject->addDecorator(new PackageDecorators\SellableDecorator($package, $stocksByPackage));
                $dataObject->addDecorator(new PackageDecorators\LabelDecorator($package));
                $dataObject->addDecorator(new PackageDecorators\WithItemsDecorator($package, $stocksByPackage));
                $dataObject->addDecorator(new PackageDecorators\WithVariantsDecorator($package, $stocksByPackage));

                array_push($packagesResult, $dataObject);
            }

            $collection->addAttributes('packages', $packagesResult);
        }

        $collection->addAttributes('method', $method);

        return response()->json($collection);
    }

    public function xhrFindVariantGroups(Request $request)
    {
        $product = Product::find($request->get('product'));

        if (!$product) {
            return response()->json();
        }

        $collection = new CollectionDataObject();
        $collection->setKey('variantGroups');

        foreach ($this->variantRepo->findGroupByProduct($product) as $variantGroup) {
            $collection->add(new ProductVariantGroup($variantGroup));
        }

        return response()->json($collection);
    }
}