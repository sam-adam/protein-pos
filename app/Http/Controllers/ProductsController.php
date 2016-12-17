<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduct;
use App\Http\Requests\StoreProductInventory;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\MovementService;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $productByBarcode = Product::where('barcode', '=', $query)->first();

            if ($productByBarcode) {
                $products = [$productByBarcode];
            } else {
                $productsQuery = $productsQuery->where('products.name', 'LIKE', "%{$query}%")
                    ->orWhere('products.code', 'LIKE', "%{$query}%");
            }
        }

        if (!$products && ($categoryId = $request->get('category'))) {
            if ($categoryId === 'uncategorized') {
                $productsQuery = $productsQuery->whereNull('products.product_category_id');
            } else {
                $productsQuery = $productsQuery->orWhere('products.product_category_id', '=', $categoryId);
                $productsQuery = $productsQuery->leftJoin('product_categories AS child', function (JoinClause $query) use ($categoryId) {
                    return $query->on('child.id', '=', 'products.product_category_id')
                        ->where('child.parent_id', '=', $categoryId);
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

        return view('products.index', [
            'products'   => $products,
            'categories' => ProductCategory::with('parent')->get(),
            'brands'     => Brand::all()
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => ProductCategory::with('parent')->get(),
            'brands'     => Brand::all()
        ]);
    }

    public function store(StoreProduct $request)
    {
        $brand    = Brand::find($request->get('brand'));
        $category = ProductCategory::find($request->get('category'));

        $newProduct                      = new Product();
        $newProduct->name                = $request->get('name');
        $newProduct->price               = $request->get('price') ?: 0;
        $newProduct->brand_id            = $brand ? $brand->id : null;
        $newProduct->product_category_id = $category ? $category->id : null;
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
            ->orderBy('expired_at', 'desc')
            ->get();
        $movements   = InventoryMovement::branch(Auth::user()->branch)
            ->select('inventory_movements.*')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->where('inventory_movement_items.product_id', '=', $productId)
            ->groupBy('inventory_movements.id')
            ->get();

        foreach ($movements as $movement) {
            $movement->direction = $movement->from_branch_id == Auth::user()->branch_id
                ? 'Out'
                : 'In';
        }

        return view('products.show', [
            'now'                       => Carbon::now(),
            'product'                   => $product,
            'inventories'               => $inventories,
            'movements'                 => $movements,
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
            'brands'     => Brand::all()
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

        $product->name                = $request->get('name');
        $product->price               = $request->get('price') ?: 0;
        $product->code                = $request->get('code');
        $product->barcode             = $request->get('barcode');
        $product->brand_id            = $brand ? $brand->id : $product->brand_id;
        $product->product_category_id = $category ? $category->id : $product->product_category_id;
        $product->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product edited');
    }

    public function addInventory(StoreProductInventory $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        DB::transaction(function () use ($request, $productId) {
            $productItems               = $request->all();
            $productItems['product_id'] = $productId;

            return $this->movementService->createMovement(
                Auth::user()->branch,
                [$productItems],
                null,
                $request->get('remark'),
                $request->get('movement_effective_at')
            );
        });

        return redirect()->back()->with('flashes.success', 'Movement created');
    }
}