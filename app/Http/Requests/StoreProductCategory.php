<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class StoreProductCategory extends FormRequest
{
    protected $category;

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
        $category   = $this->getCategory();
        $categoryId = $category->id;
        $rules      = [
            'name' => "bail|required|unique:brands,name,{$categoryId},id,deleted_at,NULL"
        ];

        if ($this->isMethod('put')) {
            $rules['parent_id'] = 'bail|present|valid_child';
        }

        return $rules;
    }

    /** {@inheritDoc} */
    public function validator(ValidationFactory $factory)
    {
        $factory->extend('valid_child', function ($attribute, $value, $parameters) {
            if ($category = $this->getCategory()) {
                if (((int) $category->parent_id !== (int) $value) && $category->children()->exists()) {
                    return false;
                }
            }

            return true;
        }, 'Cannot assign parent to root categories with child');
        $factory->extend('valid_parent', function ($attribute, $value, $parameters) {
            if ($category = $this->getCategory()) {
                if ($parent = ProductCategory::find($value)) {
                    return ($parent->parent_id === null);
                }
            }

            return true;
        }, 'Parent is not root category');

        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }

    protected function getCategory()
    {
        if (!isset($this->category)) {
            $this->category = ProductCategory::find(Route::input('category'));
        }

        return $this->category;
    }
}
