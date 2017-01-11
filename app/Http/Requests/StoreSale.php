<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Repository\InventoryRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class StoreSale
 *
 * @package App\Http\Requests
 */
class StoreSale extends FormRequest
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
            'products.*.id'       => 'bail|required|exists:products,id',
            'products.*.quantity' => 'bail|required|numeric|min:1|stock_is_sufficient',
            'products.*.discount' => 'bail|required|numeric|min:0|max:100',
            'customer_id'         => 'bail|required|exists:customers,id',
            'sales_discount'      => 'bail|required|numeric|min:0|max:100'
        ];

        return $rules;
    }

    /** {@inheritDoc} */
    public function validator(ValidationFactory $factory)
    {
        /** @var InventoryRepository $inventoryRepo */
        $inventoryRepo = $this->container->make(InventoryRepository::class);

        $factory->extend('stock_is_sufficient', function ($attribute, $value) use ($inventoryRepo) {
            $index     = data_get(explode('.', $attribute), 1);
            $productId = data_get($this->get("products"), "{$index}.id");
            $product   = Product::findOrFail($productId);

            return $inventoryRepo->checkIfStockSufficient($product, $value, Auth::user()->branch);
        }, 'Insufficient stock');

        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }
}
