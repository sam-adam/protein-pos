<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RemoveInventory extends FormRequest
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
        $rules = [
            'inventory_id' => 'bail|required|exists:inventories,id',
            'quantity'     => 'bail|required|numeric|min:1|max:'.Inventory::inBranch(Auth::user()->branch)
                    ->where('id', '=', $this->get('inventory_id'))
                    ->sum('stock')
        ];

        return $rules;
    }
}
