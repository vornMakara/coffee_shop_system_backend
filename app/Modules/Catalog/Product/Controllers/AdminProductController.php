<?php

namespace App\Modules\Catalog\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Product\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:50|unique:products',
            'sku' => 'nullable|string|max:50|unique:products',
            'is_active' => 'boolean',
            'track_inventory' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $product = Product::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $id,
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $id,
            'is_active' => 'boolean',
            'track_inventory' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $product->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully.',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.'
        ]);
    }

    public function syncModifiers(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'modifier_group_ids' => 'required|array',
            'modifier_group_ids.*' => 'exists:modifier_groups,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // We use DB facade to sync since the pivot table doesn't have a standard model defined yet
        \Illuminate\Support\Facades\DB::table('product_modifier_mapping')
            ->where('product_id', $id)
            ->delete();

        $inserts = [];
        foreach ($request->modifier_group_ids as $index => $groupId) {
            $inserts[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'product_id' => $id,
                'modifier_group_id' => $groupId,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($inserts)) {
            \Illuminate\Support\Facades\DB::table('product_modifier_mapping')->insert($inserts);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product modifiers updated successfully.'
        ]);
    }
}
