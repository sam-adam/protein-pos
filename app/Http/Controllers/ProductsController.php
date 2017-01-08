<?php

namespace App\Http\Controllers;

use App\DTO\Collection;
use App\DTO\ProductWithStock;
use App\Http\Requests\MoveInventoryToOtherBranch;
use App\Http\Requests\RemoveInventory;
use App\Http\Requests\StoreProduct;
use App\Http\Requests\AddProductMovement;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use App\Models\InventoryRemoval;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariantGroup;
use App\Repository\InventoryRepository;
use App\Services\MovementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
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
    protected $inventoryRepo;

    public function __construct(MovementService $movementService, InventoryRepository $inventoryRepo)
    {
        parent::__construct();

        $this->movementService = $movementService;
        $this->inventoryRepo   = $inventoryRepo;
    }

    public function index(Request $request)
    {
        $query         = $request->get('query');
        $perPage       = 24;
        $products      = null;
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

        foreach ($products as $product) {
            $product->stock       = BranchInventory::product($product)->sum('stock');
            $product->branchStock = BranchInventory::inBranch(Auth::user()->branch)
                ->product($product)
                ->sum('stock');
        }

        Session::put('last_product_page', $request->fullUrl());

        return view('products.index', [
            'products'     => $products->appends(Input::except('page')),
            'categories'   => $categories,
            'categoryTree' => $categoryTree,
            'brands'       => Brand::orderBy('name', 'asc')->get(),
            'showMode'     => count($request->all()) === 0 ? 'category' : 'product'
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'brands'     => Brand::orderBy('name', 'asc')->get(),
            'variants'   => ProductVariantGroup::all(),
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
            $variant  = null;
        } else {
            $brand    = Brand::find($request->get('brand'));
            $category = ProductCategory::find($request->get('category'));
            $variant  = ProductVariantGroup::find($request->get('product_variant_group_id'));
        }

        $newProduct->brand_id                 = $brand ? $brand->id : null;
        $newProduct->product_category_id      = $category ? $category->id : null;
        $newProduct->product_variant_group_id = $variant ? $variant->id : null;
        $newProduct->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product added');
    }

    public function show($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect(route('products.index'))->with('flashes.error', 'Product not found');
        }

        $movementLabels = [];

        $branches    = Branch::licensed()->get()->keyBy('id');
        $inventories = Inventory::with('branchItems')
            ->where('product_id', '=', $product->id)
            ->orderBy('priority', 'asc')
            ->get()
            ->map(function (Inventory $inventory) {
                $inventory->stock = $inventory->branchItems->sum('stock');

                return $inventory;
            });
        $movements   = InventoryMovement::with('items')
            ->branch(Auth::user()->branch)
            ->select('inventory_movements.*')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->where('inventory_movement_items.product_id', '=', $productId)
            ->groupBy('inventory_movements.id')
            ->orderBy('inventory_movements.movement_effective_at', 'desc')
            ->get();
        $removals    = InventoryRemoval::select('inventory_removals.*')
            ->join('branch_inventories', 'branch_inventories.id', '=', 'inventory_removals.branch_inventory_id')
            ->join('inventories', 'branch_inventories.inventory_id', '=', 'inventories.id')
            ->where('branch_inventories.branch_id', '=', Auth::user()->branch_id)
            ->where('inventories.product_id', '=', $productId)
            ->orderBy('inventory_removals.created_at', 'desc')
            ->get();

        foreach ($movements as $movement) {
            if ($movement->from_branch_id) {
                $movementLabel = 'Inventory transfer '.($movement->from_branch_id == Auth::user()->branch_id ? 'to ' : 'from ').$movement->to->name;
            } else {
                $movementLabel = 'Inventory import at '.$movement->to->name;
            }

            $movementLabels[] = [
                'id'         => $movement->id,
                'label'      => $movementLabel,
                'quantity'   => $movement->items->filter(function (InventoryMovementItem $movementItem) use ($productId) {
                    return $movementItem->product_id == $productId;
                })->sum('quantity'),
                'date'       => $movement->movement_effective_at,
                'dateString' => $movement->movement_effective_at->toDayDateTimeString(),
                'actor'      => $movement->creator->name,
                'remark'     => $movement->remark
            ];
        }

        foreach ($removals as $inventoryRemoval) {
            $movementLabels[] = [
                'id'         => $inventoryRemoval->id,
                'label'      => 'Removed',
                'quantity'   => $inventoryRemoval->quantity,
                'date'       => $inventoryRemoval->created_at,
                'dateString' => $inventoryRemoval->created_at->toDayDateTimeString(),
                'actor'      => $inventoryRemoval->creator->name,
                'remark'     => $inventoryRemoval->remark
            ];
        }

        uasort($movementLabels, function ($first, $second) {
            return $first['date']->lt($second['date']);
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
            $branch->expiredBranchInventories = $branch->branchInventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
            $branch->closestExpiredInventory  = $branch->branchInventories->filter(function (Inventory $inventory) { return $inventory->isExpired() === false; })->first();
        }

        $expiredInventories = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
        $closestExpired     = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired() === false; })->first();

        return view('products.show', [
            'now'                       => Carbon::now(),
            'product'                   => $product,
            'branches'                  => $branches,
            'inventories'               => $inventories,
            'allowedMovementQuantity'   => $branches[Auth::user()->branch_id]->branchInventories->sum('stock'),
            'expiredInventories'        => $expiredInventories,
            'closestExpired'            => $closestExpired,
            'movements'                 => $movementLabels,
            'currentBranch'             => $branches[Auth::user()->branch_id],
            'otherBranches'             => $branches->except(Auth::user()->branch_id),
            'defaultMovementDate'       => Carbon::now(),
            'defaultExpiredDate'        => \Carbon\Carbon::now()->addMonth(1),
            'defaultExpiryReminderDate' => \Carbon\Carbon::now()->addMonth(1)->subWeek(1)
        ]);
    }

    public function edit($productId)
    {
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
            'brands'     => Brand::orderBy('name', 'asc')->get(),
            'variants'   => ProductVariantGroup::all()
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
                $variant  = null;
            } else {
                $brand    = Brand::find($request->get('brand'));
                $category = ProductCategory::find($request->get('category'));
                $variant  = ProductVariantGroup::find($request->get('product_variant_group_id'));
            }

            $product->brand_id                 = $brand ? $brand->id : null;
            $product->product_category_id      = $category ? $category->id : null;
            $product->product_variant_group_id = $variant ? $variant->id : null;
            $product->is_service               = $request->get('is_service') ?: false;
            $product->saveOrFail();
        });

        return redirect(route('products.show', $productId))->with('flashes.success', 'Product edited');
    }

    public function addInventory(AddProductMovement $request, $productId)
    {
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
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($request, $product) {
            $branchInventory = BranchInventory::find($request->get('inventory_id'));

            if (!$branchInventory) {
                throw new ModelNotFoundException(BranchInventory::class);
            }

            $newRemoval                      = new InventoryRemoval();
            $newRemoval->branch_inventory_id = $branchInventory->id;
            $newRemoval->quantity            = $request->get('quantity');
            $newRemoval->pre_adjusted_stock  = $branchInventory->stock;
            $newRemoval->remark              = $request->get('remark');
            $newRemoval->saveOrFail();

            $branchInventory->stock -= $newRemoval->quantity;
            $branchInventory->saveOrFail();
        });

        return redirect()->back()->with('flashes.success', 'Inventory removed');
    }

    public function xhrSearch(Request $request)
    {
        $withStockTransformer = function (Product $product) {
            return new ProductWithStock($product, $product->stock);
        };

        $branch = Auth::user()->branch;

        if ($request->get('method') === 'barcode') {
            if ($product = Product::whereBarcode($request->get('query'))->first()) {
                return response()->json(new Collection($this->inventoryRepo->countAvailableStock([$product], $branch), $withStockTransformer));
            }
        } else {
            $products = Product::with('category', 'brand', 'item')->select('products.*')
                ->where('products.name', 'LIKE', "%{$request->get('query')}%")
                ->orWhere('products.code', 'LIKE', "%{$request->get('query')}%")
                ->limit(5)
                ->get();

            return response()->json(new Collection($this->inventoryRepo->countAvailableStock($products, $branch), $withStockTransformer));
        }

        return response()->json([]);
    }
}