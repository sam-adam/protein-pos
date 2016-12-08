<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategory;
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
        return view('categories.index', ['categories' => ProductCategory::paginate()]);
    }

    public function create()
    {
        return view('categories.create', ['roots' => ProductCategory::roots()->get()]);
    }

    public function store(StoreProductCategory $request)
    {
        $newCategory       = new ProductCategory();
        $newCategory->name = $request->get('name');
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
}