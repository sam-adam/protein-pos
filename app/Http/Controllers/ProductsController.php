<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduct;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProductsController
 *
 * @package App\Http\Controllers
 */
class ProductsController extends AuthenticatedController
{
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
        return view('products.create', ['products' => Product::roots()->get()]);
    }

    public function store(StoreProduct $request)
    {
        $newProduct       = new Product();
        $newProduct->name = $request->get('name');
        $newProduct->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product added');
    }

    public function edit($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        return view('products.edit', [
            'product' => $product,
            'roots'   => Product::roots()->get()->except($productId)
        ]);
    }

    public function update(StoreProduct $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('flashes.error', 'Product not found');
        }

        $product->name      = $request->get('name');
        $product->parent_id = $request->get('parent_id') ?: null;
        $product->saveOrFail();

        return redirect(route('products.index'))->with('flashes.success', 'Product edited');
    }
}