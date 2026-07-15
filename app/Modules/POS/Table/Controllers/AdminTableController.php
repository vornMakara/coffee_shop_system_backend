<?php

namespace App\Modules\POS\Table\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Table\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTableController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'table_category_id' => 'required|exists:table_categories,id',
            'capacity' => 'integer|min:1|default:2',
            'status' => 'string|in:available,occupied,reserved,cleaning|default:available',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $table = Table::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Table created successfully.',
            'data' => $table
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $table = Table::find($id);

        if (!$table) {
            return response()->json(['status' => 'error', 'message' => 'Table not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:50',
            'table_category_id' => 'sometimes|required|exists:table_categories,id',
            'capacity' => 'integer|min:1',
            'status' => 'string|in:available,occupied,reserved,cleaning',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $table->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Table updated successfully.',
            'data' => $table
        ]);
    }

    public function destroy($id)
    {
        $table = Table::find($id);

        if (!$table) {
            return response()->json(['status' => 'error', 'message' => 'Table not found'], 404);
        }

        $table->delete(); // Soft delete

        return response()->json([
            'status' => 'success',
            'message' => 'Table deleted successfully.'
        ]);
    }
}
