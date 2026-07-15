<?php

namespace App\Modules\Catalog\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'selling_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ];
    }
}
