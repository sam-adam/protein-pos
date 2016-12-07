<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrand;
use App\Models\Brand;

/**
 * Class BrandsController
 *
 * @package App\Http\Controllers
 */
class BrandsController extends AuthenticatedController
{
    public function index()
    {
        return view('brands.index', ['brands' => Brand::paginate()]);
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(StoreBrand $request)
    {
        $newBrand       = new Brand();
        $newBrand->name = $request->get('name');
        $newBrand->saveOrFail();

        return redirect(route('brands.index'))->with('flashes.success', 'Brand added');
    }

    public function edit($brandId)
    {
        $brand = Brand::find($brandId);

        if (!$brand) {
            return redirect()->back()->with('flashes.error', 'Brand not found');
        }

        return view('brands.edit', ['brand' => $brand]);
    }

    public function update(StoreBrand $request, $brandId)
    {
        $brand = Brand::find($brandId);

        if (!$brand) {
            return redirect()->back()->with('flashes.error', 'Brand not found');
        }

        $brand->name = $request->get('name');
        $brand->saveOrFail();

        return redirect(route('brands.index'))->with('flashes.success', 'Brand edited');
    }
}