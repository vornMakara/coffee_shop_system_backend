<?php

namespace App\Modules\Auth\Role\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Auth\Role\Requests\Admin\StoreRoleRequest;
use App\Modules\Auth\Role\Requests\Admin\UpdateRoleRequest;
use App\Modules\Auth\Role\Requests\Admin\UpdateRolePermissionsRequest;
use App\Modules\Auth\Role\Resources\Admin\RoleResource;
use App\Modules\Auth\Role\Services\Admin\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/permissions",
     *     tags={"Admin RBAC"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Permissions",
     *     description="Retrieve all available permissions. Requires `admin.rbac` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Permissions retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="name", type="string", example="admin.users"),
     *                     @OA\Property(property="group", type="string", example="Admin")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function permissions()
    {
        $permissions = \App\Modules\Auth\Permission\Models\Permission::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Permissions retrieved successfully.',
            'data' => $permissions
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/roles",
     *     tags={"Admin RBAC"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Roles",
     *     description="Retrieve all roles and their permissions. Requires `admin.rbac` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="name", type="string", example="Manager"),
     *                     @OA\Property(
     *                         property="permissions",
     *                         type="array",
     *                         @OA\Items(type="string", example="admin.users")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json([
            'status' => 'success',
            'message' => 'Roles retrieved successfully.',
            'data' => RoleResource::collection($roles)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/roles",
     *     tags={"Admin RBAC"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Role",
     *     description="Create a new Role. Requires `admin.rbac` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Manager"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="admin.users")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role created successfully.")
     *         )
     *     )
     * )
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully.',
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/roles/{id}/permissions",
     *     tags={"Admin RBAC"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Role Permissions",
     *     description="Update permissions for a specific role. Requires `admin.rbac` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="admin.users")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Permissions updated successfully.")
     *         )
     *     )
     * )
     */
    public function updatePermissions(UpdateRolePermissionsRequest $request, $id)
    {
        $role = \App\Modules\Auth\Role\Models\Role::findOrFail($id);
        
        $role = $this->roleService->updateRolePermissions($role, $request->validated()['permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'Permissions updated successfully.',
            'data' => new RoleResource($role)
        ]);
    }
    
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = \App\Modules\Auth\Role\Models\Role::findOrFail($id);
        $role = $this->roleService->updateRole($role, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully.',
            'data' => new RoleResource($role)
        ]);
    }
}
