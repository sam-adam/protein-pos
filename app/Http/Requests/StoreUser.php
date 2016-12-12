<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

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
        $rules = [
            'role'                    => 'bail|required|in:cashier,manager,admin,tech_admin',
            'branch_id'               => 'bail|required|exists:branches,id',
            'max_percentage_discount' => 'bail|present|numeric|between:0,100',
            'max_price_discount'      => 'bail|present|numeric'
        ];

        if ($this->isMethod('post')) {
            $rules['name']     = 'bail|required|unique:users,name';
            $rules['username'] = 'bail|required|alpha_dash|unique:users,username';
            $rules['password'] = 'bail|required|min:6';
        }

        return $rules;
    }
}
