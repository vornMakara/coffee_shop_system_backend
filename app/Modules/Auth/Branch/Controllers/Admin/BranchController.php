<?php

namespace App\Modules\Auth\Branch\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Auth\Branch\Requests\Admin\StoreBranchRequest;
use App\Modules\Auth\Branch\Requests\Admin\UpdateBranchRequest;
use App\Modules\Auth\Branch\Resources\Admin\BranchResource;
use App\Modules\Auth\Branch\Services\Admin\BranchService;

class BranchController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/branches",
     *     tags={"Branches Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Branches",
     *     description="Retrieve a paginated list of branches. Requires `admin.pos_setup` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Branches retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Branches retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Main Street Branch"),
     *                         @OA\Property(property="location", type="string", example="123 Main St, City"),
     *                         @OA\Property(property="contact_number", type="string", example="+1234567890"),
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
        $branches = $this->branchService->getPaginated(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Branches retrieved successfully.',
            'data' => BranchResource::collection($branches)->response()->getData(true)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/branches",
     *     tags={"Branches Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Branch",
     *     description="Create a new Branch. Requires `admin.pos_setup` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Main Street Branch"),
     *             @OA\Property(property="location", type="string", example="123 Main St, City"),
     *             @OA\Property(property="contact_number", type="string", example="+1234567890"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Created successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Main Street Branch"),
     *                 @OA\Property(property="location", type="string", example="123 Main St, City"),
     *                 @OA\Property(property="contact_number", type="string", example="+1234567890"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = $this->branchService->createBranch($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => new BranchResource($branch)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/branches/{id}",
     *     tags={"Branches Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Branch",
     *     description="Retrieve a single Branch by ID. Requires `admin.pos_setup` permission.",
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
     *                 @OA\Property(property="name", type="string", example="Main Street Branch"),
     *                 @OA\Property(property="location", type="string", example="123 Main St, City"),
     *                 @OA\Property(property="contact_number", type="string", example="+1234567890"),
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
        $branch = \App\Modules\Auth\Branch\Models\Branch::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => new BranchResource($branch)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/branches/{id}",
     *     tags={"Branches Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Branch",
     *     description="Update an existing Branch. Requires `admin.pos_setup` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location", type="string", example="123 Main St, City"),
     *             @OA\Property(property="contact_number", type="string", example="+1234567890"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Main Street Branch"),
     *                 @OA\Property(property="location", type="string", example="123 Main St, City"),
     *                 @OA\Property(property="contact_number", type="string", example="+1234567890"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateBranchRequest $request, $id)
    {
        $branch = \App\Modules\Auth\Branch\Models\Branch::findOrFail($id);
        $branch = $this->branchService->updateBranch($branch, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => new BranchResource($branch)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/branches/{id}",
     *     tags={"Branches Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Branch",
     *     description="Soft-delete a Branch. Requires `admin.pos_setup` permission.",
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
        $branch = \App\Modules\Auth\Branch\Models\Branch::findOrFail($id);
        $this->branchService->deleteBranch($branch);

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
