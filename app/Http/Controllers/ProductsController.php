<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveInventoryToOtherBranch;
use App\Http\Requests\StoreProduct;
use App\Http\Requests\AddProductMovement;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
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
use Illuminate\Support\Facades\URL;

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

        if ($query) {
            $productByBarcode = Product::where('barcode', '=', $query)->paginate();

            if ($productByBarcode->count() === 0) {
                $productsQuery = $productsQuery->where(function ($subWhere) use($query) {
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
                $productsQuery = $productsQuery->orWhere(function ($subWhere) use ($categoryId) {
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
            $product->stock = Inventory::inBranch(Auth::user()->branch)
                ->where('product_id', '=', $product->id)
                ->sum('stock');
        }

        Session::put('last_product_page', $request->fullUrl());

        return view('products.index', [
            'products'   => $products->appends(Input::except('page')),
            'categories' => ProductCategory::with('parent')->orderBy('parent_id', 'asc')->orderBy('name', 'asc')->get(),
            'brands'     => Brand::orderBy('name', 'asc')->get()
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => ProductCategory::with('parent')->get(),
            'brands'     => Brand::all(),
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

        $inventories = Inventory::inBranch(Auth::user()->branch)
            ->where('product_id', '=', $product->id)
            ->orderBy('expired_at', 'asc')
            ->get();
        $movements   = InventoryMovement::with('items')
            ->branch(Auth::user()->branch)
            ->select('inventory_movements.*')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->where('inventory_movement_items.product_id', '=', $productId)
            ->groupBy('inventory_movements.id')
            ->orderBy('inventory_movements.movement_effective_at', 'desc')
            ->get();

        foreach ($movements as $movement) {
            $movement->quantity  = $movement->items->filter(function (InventoryMovementItem $movementItem) use ($productId) {
                return $movementItem->product_id == $productId;
            })->sum('quantity');
            $movement->direction = $movement->from_branch_id == Auth::user()->branch_id
                ? 'Out'
                : 'In';
        }

        $expiredInventories = $inventories->filter(function (Inventory $inventory) { return $inventory->isExpired(); });
        $closestExpired     = $inventories->filter(function (Inventory $inventory) { return !$inventory->isExpired(); })->first();

        return view('products.show', [
            'now'                       => Carbon::now(),
            'product'                   => $product,
            'inventories'               => $inventories,
            'expiredInventories'        => $expiredInventories,
            'closestExpired'            => $closestExpired,
            'movements'                 => $movements,
            'otherBranches'             => Branch::licensed()->active()->get()->except(Auth::user()->branch_id),
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
            'categories' => ProductCategory::with('parent')->get(),
            'brands'     => Brand::all(),
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
            $inventory                            = Inventory::find($request->get('inventory_id'));
            $movementItem                         = $inventory->getAttributes();
            $movementItem['product_id']           = $productId;
            $movementItem['source_inventory_id']  = $inventory->id;
            $movementItem['expire_date']          = $inventory->expired_at->toDateString();
            $movementItem['expiry_reminder_date'] = $inventory->expiry_reminder_date->toDateString();
            $movementItem['quantity']             = $request->get('quantity');

            return $this->movementService->createMovement(
                Branch::find($request->get('branch_id')),
                [$movementItem],
                Auth::user()->branch,
                $request->get('remark')
            );
        });

        return redirect()->back()->with('flashes.success', 'Inventory moved');
    }
}