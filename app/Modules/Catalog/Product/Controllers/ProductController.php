<?php

namespace App\Modules\Catalog\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Catalog\Product\Requests\StoreProductRequest;
use App\Modules\Catalog\Product\Requests\UpdateProductRequest;
use App\Modules\Catalog\Product\Resources\ProductResource;
use App\Modules\Catalog\Product\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Catalog & Menu"},
     *     summary="List Products",
     *     description="Retrieves products for the main POS grid.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="name", type="string", example="Latte"),
     *                     @OA\Property(property="price", type="number", format="float", example=3.50),
     *                     @OA\Property(property="category_id", type="string", example="uuid")
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
            'category_id' => 'sometimes|uuid',
            'is_active' => 'sometimes|boolean'
        ]);

        $products = $this->productService->getProducts($request->only(['branch_id', 'category_id', 'is_active']));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully.',
            'data' => ProductResource::collection($products)
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product)
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
            'data' => new ProductResource($product)
        ]);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $product = Product::findOrFail($id);
        $product = $this->productService->updateProduct($product, $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product)
        ]);
    }

    public function destroy($id)
    {
        if (!\Str::isUuid($id)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid ID format.'], 400);
        }

        $product = Product::findOrFail($id);
        $this->productService->deleteProduct($product);
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.'
        ], 200);
    }
}
