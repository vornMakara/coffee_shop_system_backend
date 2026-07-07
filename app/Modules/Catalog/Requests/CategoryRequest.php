<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => 'nullable|uuid',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'name' => 'required|string|max:100',
            'name_kh' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
