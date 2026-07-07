<?php

namespace App\Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Requests\ProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'sometimes|uuid',
            'category_id' => 'sometimes|uuid',
            'is_active' => 'sometimes|boolean'
        ]);

        $query = Product::query();
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully.',
            'data' => $query->get()
        ]);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }

    public function show($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $product = Product::findOrFail($id);
        // Optionally load modifiers or relations here if required by backend spec
        return response()->json([
            'status' => 'success',
            'message' => 'Product retrieved successfully.',
            'data' => $product
        ]);
    }

    public function update(ProductRequest $request, $id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $product = Product::findOrFail($id);
        $product->update($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully.',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.'
        ], 200);
    }
}
