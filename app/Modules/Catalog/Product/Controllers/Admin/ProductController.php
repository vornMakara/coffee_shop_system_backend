<?php

namespace App\Modules\Catalog\Product\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/products",
     *     tags={"Products Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Products",
     *     description="Retrieve a paginated list of products. Requires `admin.catalog` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Caffe Latte"),
     *                         @OA\Property(property="category_id", type="string", example="uuid"),
     *                         @OA\Property(property="price", type="number", format="float", example=4.50),
     *                         @OA\Property(property="sku", type="string", example="HOT-LAT-01"),
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
        $products = \App\Modules\Catalog\Product\Models\Product::with(['category', 'branch'])->paginate(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully.',
            'data' => $products
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/products",
     *     tags={"Products Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Product",
     *     description="Create a new Product. Requires `admin.catalog` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="category_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", example="Caffe Latte"),
     *             @OA\Property(property="description", type="string", example="Delicious latte"),
     *             @OA\Property(property="price", type="number", format="float", example=4.50),
     *             @OA\Property(property="sku", type="string", example="HOT-LAT-01"),
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
            'category_id' => 'required|uuid|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'is_active' => 'boolean'
        ]);

        $product = \App\Modules\Catalog\Product\Models\Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => $product->load(['category', 'branch'])
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Products Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Product",
     *     description="Retrieve a single Product by ID. Requires `admin.catalog` permission.",
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
     *                 @OA\Property(property="name", type="string", example="Caffe Latte"),
     *                 @OA\Property(property="category_id", type="string", example="uuid"),
     *                 @OA\Property(property="price", type="number", format="float", example=4.50),
     *                 @OA\Property(property="sku", type="string", example="HOT-LAT-01"),
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
        $product = \App\Modules\Catalog\Product\Models\Product::with(['category', 'branch'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => $product
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Products Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Product",
     *     description="Update an existing Product. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", example="Caffe Latte"),
     *             @OA\Property(property="description", type="string", example="Delicious latte"),
     *             @OA\Property(property="price", type="number", format="float", example=4.50),
     *             @OA\Property(property="sku", type="string", example="HOT-LAT-01"),
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
        $product = \App\Modules\Catalog\Product\Models\Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:categories,id',
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $id,
            'is_active' => 'boolean'
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => $product->load(['category', 'branch'])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Products Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Product",
     *     description="Soft-delete a Product. Requires `admin.catalog` permission.",
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
        $product = \App\Modules\Catalog\Product\Models\Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
