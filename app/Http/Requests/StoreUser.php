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
        $user  = User::find(Route::input('user'));
        $rules = [
            'role'                  => 'bail|required|in:cashier,manager,admin,tech_admin',
            'branch_id'             => 'bail|required|exists:branches,id',
            'minimum_discount_type' => 'bail|required_with:minimum_discount',
            'maximum_discount_type' => 'bail|required_with:maximum_discount'
        ];

        if ($this->isMethod('post')) {
            $rules['name']     = 'bail|required|unique:users,name'.($user ? ','.$user->name : '');
            $rules['username'] = 'bail|required|alpha_dash|unique:users,username'.($user ? ','.$user->username : '');
            $rules['password'] = 'bail|required|min:6';
        } elseif ($this->isMethod('put')) {
            $rules['password'] = 'bail|present|min:6';
        }

        return $rules;
    }
}
