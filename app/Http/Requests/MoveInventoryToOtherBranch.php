<?php

namespace App\Http\Requests;

use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class MoveInventoryToOtherBranch extends FormRequest
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
        $product = Product::findOrFail(Route::input('product'));

        return [
            'branch_id' => 'bail|required|exists:branches,id',
            'quantity'  => 'bail|required|numeric|min:1|max:'.BranchInventory::inBranch(Auth::user()->branch)
                    ->product($product)
                    ->sum('stock')
        ];
    }
}
