<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduct;
use App\Models\Product;

/**
 * Class ProductsController
 *
 * @package App\Http\Controllers
 */
class ProductsController extends AuthenticatedController
{
    public function index()
    {
        return view('products.index', ['roots' => Product::paginate()]);
    }

    public function create()
    {
        return view('products.create', ['roots' => Product::roots()->get()]);
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
            'roots'    => Product::roots()->get()->except($productId)
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