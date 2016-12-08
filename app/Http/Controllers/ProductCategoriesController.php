<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategory;
use App\Models\Product;
use App\Models\ProductCategory;

/**
 * Class ProductCategoriesController
 *
 * @package App\Http\Controllers
 */
class ProductCategoriesController extends AuthenticatedController
{
    public function index()
    {
        return view('categories.index', ['roots' => ProductCategory::roots()->with('children')->get()]);
    }

    public function create()
    {
        return view('categories.create', ['roots' => ProductCategory::roots()->get()]);
    }

    public function store(StoreProductCategory $request)
    {
        $newCategory            = new ProductCategory();
        $newCategory->parent_id = $request->get('parent_id') ?: null;
        $newCategory->name      = $request->get('name');
        $newCategory->saveOrFail();

        return redirect(route('categories.index'))->with('flashes.success', 'Category added');
    }

    public function edit($categoryId)
    {
        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return redirect()->back()->with('flashes.error', 'Category not found');
        }

        return view('categories.edit', [
            'category' => $category,
            'roots'    => ProductCategory::roots()->get()->except($categoryId)
        ]);
    }

    public function update(StoreProductCategory $request, $categoryId)
    {
        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return redirect()->back()->with('flashes.error', 'Category not found');
        }

        $category->name      = $request->get('name');
        $category->parent_id = $request->get('parent_id') ?: null;
        $category->saveOrFail();

        return redirect(route('categories.index'))->with('flashes.success', 'Category edited');
    }

    public function destroy($categoryId)
    {
        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return redirect()->back()->with('flashes.error', 'Category not found');
        }

        $isCategoryUsed = Product::where('product_category_id', '=', $category->id)->exists();

        if (!$isCategoryUsed && $category->children->count() > 0) {
            $isCategoryUsed = Product::whereIn('product_category_id', '=', $category->children->pluck('id'))->exists();
        }

        if ($isCategoryUsed) {
            return redirect()->back()->with('flashes.error', 'Cannot delete used category');
        }

        $category->delete();

        return redirect(route('categories.index'))->with('flashes.success', 'Category deleted');
    }
}