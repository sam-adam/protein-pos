<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveInventoryToOtherBranch;
use App\Http\Requests\RemoveInventory;
use App\Http\Requests\StoreProduct;
use App\Http\Requests\AddProductMovement;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use App\Models\InventoryRemoval;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariantGroup;
use App\Services\MovementService;
use Carbon\Carbon;
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

    public function __construct(MovementService $movementService)
    {
        parent::__construct();

        $this->movementService = $movementService;
    }

    public function index(Request $request)
    {
        $query         = $request->get('query');
        $perPage       = 24;
        $products      = null;
        $productsQuery = Product::with('category', 'brand')->select('products.*');
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
            $product->stock       = Inventory::where('product_id', '=', $product->id)->sum('stock');
            $product->branchStock = Inventory::inBranch(Auth::user()->branch)
                ->where('product_id', '=', $product->id)
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

    public function store(StoreProduct $request)
    {
        $brand    = Brand::find($request->get('brand'));
        $category = ProductCategory::find($request->get('category'));
        $variant  = ProductVariantGroup::find($request->get('product_variant_group_id'));

        $newProduct                           = new Product();
        $newProduct->name                     = $request->get('name');
        $newProduct->price                    = $request->get('price') ?: 0;
        $newProduct->brand_id                 = $brand ? $brand->id : null;
        $newProduct->product_category_id      = $category ? $category->id : null;
        $newProduct->product_variant_group_id = $variant ? $variant->id : null;
        $newProduct->is_service               = $request->get('is_service') ?: false;
        $newProduct->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product added');
    }

    public function show($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        $movements          = [];
        $branches           = Branch::licensed()->get();
        $inventories        = Inventory::where('product_id', '=', $product->id)
            ->orderBy('expired_at', 'asc')
            ->get();
        $inventoryMovements = InventoryMovement::with('items')
            ->branch(Auth::user()->branch)
            ->select('inventory_movements.*')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->where('inventory_movement_items.product_id', '=', $productId)
            ->groupBy('inventory_movements.id')
            ->orderBy('inventory_movements.movement_effective_at', 'desc')
            ->get();
        $inventoryRemovals  = InventoryRemoval::select('inventory_removals.*')
            ->join('inventories', 'inventory_removals.inventory_id', '=', 'inventories.id')
            ->where('inventories.branch_id', '=', Auth::user()->branch_id)
            ->where('inventories.product_id', '=', $productId)
            ->orderBy('inventory_removals.created_at', 'desc')
            ->get();

        foreach ($inventoryMovements as $movement) {
            if ($movement->from_branch_id) {
                $movementLabel = 'Inventory transfer '.($movement->from_branch_id == Auth::user()->branch_id ? 'to ' : 'from ').$movement->to->name;
            } else {
                $movementLabel = 'Inventory import at '.$movement->to->name;
            }

            $movements[] = [
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

        foreach ($inventoryRemovals as $inventoryRemoval) {
            $movements[] = [
                'id'         => $inventoryRemoval->id,
                'label'      => 'Removed',
                'quantity'   => $inventoryRemoval->quantity,
                'date'       => $inventoryRemoval->created_at,
                'dateString' => $inventoryRemoval->created_at->toDayDateTimeString(),
                'actor'      => $inventoryRemoval->creator->name,
                'remark'     => $inventoryRemoval->remark
            ];
        }

        uasort($movements, function ($first, $second) {
            return $first['date']->lt($second['date']);
        });

        foreach ($branches as $branch) {
            $branch->inventories        = Inventory::inBranch($branch)
                ->where('product_id', '=', $product->id)
                ->orderBy('expired_at', 'asc')
                ->get();
            $branch->expiredInventories = $branch->inventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
            $branch->closestExpired     = $branch->inventories->filter(function (Inventory $inventory) { return !$inventory->isExpired(); })->first();
        }

        $expiredInventories = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
        $closestExpired     = $inventories->filter(function (Inventory $inventory) { return !$inventory->isExpired(); })->first();

        return view('products.show', [
            'now'                       => Carbon::now(),
            'product'                   => $product,
            'branches'                  => $branches,
            'inventories'               => $inventories,
            'branchInventories'         => $inventories->filter(function (Inventory $inventory) { return $inventory->branch_id == Auth::user()->branch_id; }),
            'expiredInventories'        => $expiredInventories,
            'closestExpired'            => $closestExpired,
            'movements'                 => $movements,
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
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        return view('products.edit', [
            'product'    => $product,
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

        $brand    = Brand::find($request->get('brand'));
        $category = ProductCategory::find($request->get('category'));
        $variant  = ProductVariantGroup::find($request->get('product_variant_group_id'));

        $product->name                     = $request->get('name');
        $product->price                    = $request->get('price') ?: 0;
        $product->code                     = $request->get('code');
        $product->barcode                  = $request->get('barcode');
        $product->brand_id                 = $brand ? $brand->id : $product->brand_id;
        $product->product_category_id      = $category ? $category->id : $product->product_category_id;
        $product->product_variant_group_id = $variant ? $variant->id : null;
        $product->is_service               = $request->get('is_service') ?: false;
        $product->saveOrFail();

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

        DB::transaction(function () use ($request, $productId) {
            $movementItems     = [];
            $currentQuantity   = 0;
            $requestedQuantity = $request->get('quantity');
            $inventories       = Inventory::inBranch(Auth::user()->branch)
                ->where('product_id', '=', $productId)
                ->orderBy('expired_at', 'asc')
                ->get();

            foreach ($inventories as $inventory) {
                $stillRequiredQuantity = $requestedQuantity - $currentQuantity;

                if ($stillRequiredQuantity > 0) {
                    $movementItem    = [
                        'product_id'           => $productId,
                        'source_inventory_id'  => $inventory->id,
                        'expire_date'          => $inventory->expired_at->toDateString(),
                        'expiry_reminder_date' => $inventory->expiry_reminder_date->toDateString(),
                        'cost'                 => $inventory->cost,
                        'quantity'             => min($inventory->stock, $stillRequiredQuantity)
                    ];
                    $movementItems[] = $movementItem;

                    $currentQuantity += $movementItem['quantity'];
                }
            }

            return $this->movementService->createMovement(
                Branch::find($request->get('branch_id')),
                $movementItems,
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

        $inventory = Inventory::find($request->get('inventory_id'));

        if (!$inventory) {
            return redirect()->back()->with('flashes.error', 'Inventory not found');
        }

        DB::transaction(function () use ($request, $inventory, $productId) {
            $newRemoval                     = new InventoryRemoval();
            $newRemoval->inventory_id       = $request->get('inventory_id');
            $newRemoval->quantity           = $request->get('quantity');
            $newRemoval->pre_adjusted_stock = $inventory->stock;
            $newRemoval->remark             = $request->get('remark');
            $newRemoval->saveOrFail();

            $inventory->stock -= $newRemoval->quantity;
            $inventory->saveOrFail();
        });

        return redirect()->back()->with('flashes.success', 'Inventory removed');
    }
}