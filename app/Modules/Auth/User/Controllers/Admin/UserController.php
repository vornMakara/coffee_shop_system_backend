<?php

namespace App\Modules\Auth\User\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Auth\User\Requests\Admin\StoreUserRequest;
use App\Modules\Auth\User\Requests\Admin\UpdateUserRequest;
use App\Modules\Auth\User\Resources\Admin\UserResource;
use App\Modules\Auth\User\Services\Admin\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     tags={"Users Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Users",
     *     description="Retrieve a paginated list of users. Requires `admin.users` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="role", type="string", example="Manager"),
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
        $users = $this->userService->getPaginated(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection($users)->response()->getData(true)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users",
     *     tags={"Users Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create User",
     *     description="Create a new User. Requires `admin.users` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="role_id", type="string", format="uuid"),
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"admin.users", "admin.branches", "admin.tables", "admin.customers", "admin.catalog", "admin.rbac", "admin.pos_setup"}
     *             )
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
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/{id}",
     *     tags={"Users Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get User",
     *     description="Retrieve a single User by ID. Requires `admin.users` permission.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Details retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="Manager"),
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
        $user = \App\Modules\Auth\User\Models\User::with(['role', 'branch', 'permissions'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/users/{id}",
     *     tags={"Users Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update User",
     *     description="Update an existing User. Requires `admin.users` permission.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="role_id", type="string", format="uuid"),
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"admin.users", "admin.branches", "admin.tables", "admin.customers", "admin.catalog", "admin.rbac", "admin.pos_setup"}
     *             )
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
    public function update(UpdateUserRequest $request, $id)
    {
        $user = \App\Modules\Auth\User\Models\User::findOrFail($id);
        $user = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{id}",
     *     tags={"Users Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete User",
     *     description="Soft-delete a User. Requires `admin.users` permission.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
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
        $user = \App\Modules\Auth\User\Models\User::findOrFail($id);
        $this->userService->deleteUser($user);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
