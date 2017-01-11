<?php

namespace App\Http\Requests;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class StorePackage extends FormRequest
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
        $package   = Package::find(Route::input('package'));
        $packageId = $package ? $package->id : 'NULL';
        $rules     = [
            'name'                => "bail|required|unique:packages,name,{$packageId},id,deleted_at,NULL",
            'price'               => 'bail|required|numeric|min:0',
            'products.*.id'       => 'bail|required|exists:products,id|not_service_product',
            'products.*.quantity' => 'bail|required|numeric|min:1'
        ];

        return $rules;
    }

    /** {@inheritDoc} */
    public function validator(ValidationFactory $factory)
    {
        $factory->extend('not_service_product', function ($attribute, $value, $parameters) {
            return Product::findOrfail($value)->is_service === false;
        }, 'Cannot use service as package');

        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }
}
