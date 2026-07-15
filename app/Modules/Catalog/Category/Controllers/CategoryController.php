<?php

namespace App\Modules\Catalog\Category\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Category\Models\Category;
use App\Modules\Catalog\Category\Requests\StoreCategoryRequest;
use App\Modules\Catalog\Category\Requests\UpdateCategoryRequest;
use App\Modules\Catalog\Category\Resources\CategoryResource;
use App\Modules\Catalog\Category\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Catalog & Menu"},
     *     summary="List Product Categories",
     *     description="Retrieves categories for POS sidebar.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="name", type="string", example="Hot Coffee")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'sometimes|uuid',
            'is_active' => 'sometimes|boolean'
        ]);

        $categories = $this->categoryService->getCategories($request->only(['branch_id', 'is_active']));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => CategoryResource::collection($categories)
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category)
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
            'data' => new CategoryResource($category)
        ]);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $category = Category::findOrFail($id);
        $category = $this->categoryService->updateCategory($category, $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category)
        ]);
    }

    public function destroy($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $category = Category::findOrFail($id);
        $this->categoryService->deleteCategory($category);
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully.'
        ], 200); 
    }
}
