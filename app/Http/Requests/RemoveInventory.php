<?php

namespace App\Http\Requests;

use App\Models\BranchInventory;
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
        $inventory = Inventory::findOrFail($this->get('inventory_id'));

        $rules = [
            'inventory_id' => 'bail|required|exists:inventories,id',
            'quantity'     => 'bail|required|numeric|min:1|max:'.BranchInventory::inBranch(Auth::user()->branch)
                    ->product($inventory->product)
                    ->sum('stock')
        ];

        return $rules;
    }
}
