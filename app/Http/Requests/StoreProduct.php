<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreProduct extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product   = Product::find(Route::input('product'));
        $productId = $product ? $product->id : 'NULL';
        $rules     = [
            'name'                  => "bail|required|unique:products,name,{$productId},id,deleted_at,NULL",
            'price'                 => 'numeric',
            'brand_id'              => 'bail|exists:brands,id',
            'category_id'           => 'bail|exists:product_categories,id',
            'product_item_id'       => 'bail|exists:products,id',
            'product_item_quantity' => 'bail|required_with:product_item_id|numeric|min:1'
        ];

        return $rules;
    }
}
