<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreBrand extends FormRequest
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
        $brand = Brand::find(Route::input('brand'));
        $rules = [
            'name' => 'bail|required|unique:brands,name'
        ];

        if ($this->isMethod('put')) {
            $rules['name'] .= ','.$brand->id;
        }

        return $rules;
    }
}
