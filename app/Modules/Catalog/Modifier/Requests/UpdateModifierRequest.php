<?php

namespace App\Modules\Catalog\Modifier\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModifierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'is_required' => 'boolean',
            'min_selections' => 'integer|min:0',
            'max_selections' => 'integer|min:1'
        ];
    }
}
