<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreUser
 *
 * @package App\Http\Requests
 */
class StoreUser extends FormRequest
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
            'name'                  => 'bail|required|unique:users,name',
            'username'              => 'bail|required|unique:users,username|alpha_dash',
            'password'              => 'bail|required|min:6',
            'role'                  => 'bail|required|in:cashier,manager,admin,tech_admin',
            'branch_id'             => 'bail|required|exists:branches,id',
            'minimum_discount_type' => 'bail|required_with:minimum_discount',
            'maximum_discount_type' => 'bail|required_with:maximum_discount'
        ];
    }
}
