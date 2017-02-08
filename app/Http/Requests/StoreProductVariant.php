<?php

namespace App\Http\Requests;

use App\Models\ProductVariantGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreProductVariant extends FormRequest
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
        $variant   = ProductVariantGroup::find(Route::input('product_variant'));
        $variantId = $variant ? $variant->id : 'NULL';
        $rules     = [
            'name'     => "bail|required|unique:product_variant_groups,name,{$variantId},id,deleted_at,NULL",
            'quantity' => "bail|required|numeric|min:1"
        ];

        return $rules;
    }
}
