<?php

namespace App\Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Requests\CategoryRequest;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'sometimes|uuid',
            'is_active' => 'sometimes|boolean'
        ]);

        $query = Category::query();
        
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => $query->orderBy('sort_order')->get()
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    public function show($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $category = Category::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Category retrieved successfully.',
            'data' => $category
        ]);
    }

    public function update(CategoryRequest $request, $id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $category = Category::findOrFail($id);
        $category->update($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully.'
        ], 200); // Usually 204 has no content, changing to 200 to return the message.
    }
}
