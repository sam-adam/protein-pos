<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreCustomer extends FormRequest
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
        $customer   = Customer::find(Route::input('customer'));
        $customerId = $customer ? $customer->id : 'NULL';
        $rules      = [
            'name'              => 'bail|required',
            'email'             => "bail|email|unique:customers,email,{$customerId},id,deleted_at,NULL",
            'customer_group_id' => 'bail|exists:customer_groups,id'
        ];

        return $rules;
    }
}
