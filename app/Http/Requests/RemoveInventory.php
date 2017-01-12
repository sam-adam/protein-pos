<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\BranchInventory;
use Illuminate\Foundation\Http\FormRequest;

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
        $branchInventory = BranchInventory::findOrFail($this->get('branch_inventory_id'));

        $rules = [
            'branch_id'           => 'bail|required|exists:branches,id',
            'branch_inventory_id' => 'bail|required|exists:branch_inventories,id',
            'quantity'            => 'bail|required|numeric|min:1|max:'.BranchInventory::inBranch(Branch::findOrFail($this->get('branch_id')))
                    ->product($branchInventory->inventory->product)
                    ->sum('stock')
        ];

        return $rules;
    }
}
