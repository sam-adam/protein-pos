<?php

namespace App\Http\Requests;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ClockOut extends FormRequest
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
        $shift = Shift::find(Route::input('shift'));
        $rules = [
            'closing_balance' => 'bail|required|numeric|min:1'
        ];

        return $rules;
    }
}
