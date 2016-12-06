<?php

namespace App\Http\Requests;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class StoreBranch extends FormRequest
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
        $branch = Branch::find(Route::input('branch'));
        $rules  = [
            'cash_counters_count' => 'bail|required|numeric|min:1'
        ];

        if ($this->isMethod('post')) {
            $rules['name'] = 'bail|required|unique:branches,name';
        } elseif ($this->isMethod('put')) {
            $rules['name'] = 'bail|required|unique:branches,name,'.$branch->id;
        }

        return $rules;
    }
}
