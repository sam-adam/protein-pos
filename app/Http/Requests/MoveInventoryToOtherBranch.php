<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;

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
        $inventory = Inventory::find($this->get('inventory_id'));

        return [
            'branch_id'    => 'bail|required|exists:branches,id',
            'inventory_id' => 'bail|required|exists:inventories,id',
            'quantity'     => 'bail|required|numeric|min:1|max:'.$inventory->stock
        ];
    }
}
