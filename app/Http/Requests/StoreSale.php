<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Repository\InventoryRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

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
            'packages.*.id'       => 'bail|required|exists:packages,id',
            'customer_id'         => 'bail|required|exists:customers,id',
            'sales_discount'      => 'bail|required|numeric|min:0|can_give_discount|discount_rule'
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

            return $product->is_service
                || $inventoryRepo->checkIfStockSufficient($product, $value, Auth::user()->branch);
        }, 'Insufficient stock');

        $factory->extend('can_give_discount', function () { return Auth::user()->can_give_discount; }, 'Unauthorized to give discount');

        $factory->extend('discount_rule', function ($attribute, $value) {
            $user = Auth::user();

            if ($user->can_give_unlimited_discount) {
                return true;
            }

            switch ($this->get('sales_discount_type')) {
                case Sale::DISCOUNT_TYPE_PRICE:
                    return $value <= $user->max_price_discount;
                    break;
                default:
                    return $value <= min($user->max_percentage_discount, 100);
                    break;
            }
        }, 'Invalid discount');

        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }
}
