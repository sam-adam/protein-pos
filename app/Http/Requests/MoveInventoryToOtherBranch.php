<?php

namespace App\Http\Requests;

use App\Models\Inventory;
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
        return [
            'branch_id' => 'bail|required|exists:branches,id',
            'quantity'  => 'bail|required|numeric|min:1|max:'.Inventory::inBranch(Auth::user()->branch)
                    ->where('product_id', '=', Route::input('product'))
                    ->sum('stock')
        ];
    }
}
