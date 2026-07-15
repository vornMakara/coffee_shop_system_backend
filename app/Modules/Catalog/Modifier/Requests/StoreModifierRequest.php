<?php

namespace App\Modules\Catalog\Modifier\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModifierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'is_required' => 'boolean',
            'min_selections' => 'integer|min:0',
            'max_selections' => 'integer|min:1',
            'options' => 'required|array',
            'options.*.name' => 'required|string|max:255',
            'options.*.price_adjustment' => 'numeric|default:0'
        ];
    }
}
