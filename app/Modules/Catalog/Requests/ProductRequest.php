<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'name' => 'required|string|max:150',
            'name_kh' => 'nullable|string|max:150',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
