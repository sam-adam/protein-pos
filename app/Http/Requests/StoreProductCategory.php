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
        $brand = $this->getCategory();
        $rules = [
            'name' => 'bail|required|unique:brands,name'
        ];

        if ($this->isMethod('put')) {
            $rules['name']      .= ','.$brand->id;
            $rules['parent_id'] = 'bail|valid_child|valid_parent';
        }

        return $rules;
    }

    /** {@inheritDoc} */
    public function validator(ValidationFactory $factory)
    {
        $factory->extend('valid_child', [$this, 'validateChild'], 'Cannot assign parent to root categories with child');
        $factory->extend('valid_parent', [$this, 'validateRoot'], 'Parent is not root category');

        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }

    protected function validateChild($attribute, $value, $parameters)
    {
        if ($category = $this->getCategory()) {
            if ($value && $category->children()->exists()) {
                return false;
            }
        }

        return true;
    }

    protected function validateRoot($attribute, $value, $parameters)
    {
        if ($category = $this->getCategory()) {
            if ($parent = ProductCategory::find($value)) {
                return ($parent->parent_id === null);
            }
        }

        return true;
    }

    protected function getCategory()
    {
        if (!isset($this->category)) {
            $this->category = ProductCategory::find(Route::input('category'));
        }

        return $this->category;
    }
}
