<?php

namespace App\Modules\POS\Table\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'table_number' => 'nullable|string|max:20',
            'seating_capacity' => 'integer|min:1',
            'status' => 'string|in:available,occupied,reserved,out_of_service'
        ];
    }
}
