<?php

namespace App\Modules\Catalog\Category\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories",
     *     tags={"Categories Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Categories",
     *     description="Retrieve a paginated list of categories. Requires `admin.catalog` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Hot Beverages"),
     *                         @OA\Property(property="description", type="string", example="Coffee, Tea, and Hot Chocolate"),
     *                         @OA\Property(property="is_active", type="boolean", example=true),
     *                         @OA\Property(property="id", type="string", example="uuid"),
     *                         @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = \App\Modules\Catalog\Category\Models\Category::paginate(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => $categories
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/categories",
     *     tags={"Categories Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Category",
     *     description="Create a new Category. Requires `admin.catalog` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", example="Hot Beverages"),
     *             @OA\Property(property="description", type="string", example="Coffee, Tea, and Hot Chocolate"),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Created successfully.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'integer|default:0',
            'is_active' => 'boolean'
        ]);

        $category = \App\Modules\Catalog\Category\Models\Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Categories Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Category",
     *     description="Retrieve a single Category by ID. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Details retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Details retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Hot Beverages"),
     *                 @OA\Property(property="description", type="string", example="Coffee, Tea, and Hot Chocolate"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $category = \App\Modules\Catalog\Category\Models\Category::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => $category
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Categories Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Category",
     *     description="Update an existing Category. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Hot Beverages"),
     *             @OA\Property(property="description", type="string", example="Coffee, Tea, and Hot Chocolate"),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Updated successfully.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $category = \App\Modules\Catalog\Category\Models\Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => $category
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Categories Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Category",
     *     description="Soft-delete a Category. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $category = \App\Modules\Catalog\Category\Models\Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
