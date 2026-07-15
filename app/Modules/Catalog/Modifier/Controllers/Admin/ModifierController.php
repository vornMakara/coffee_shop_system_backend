<?php

namespace App\Modules\Catalog\Modifier\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/modifiers",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Modifiers",
     *     description="Retrieve a paginated list of modifiers. Requires `admin.catalog` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Modifiers retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Modifiers retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Extra Shot"),
     *                         @OA\Property(property="price", type="number", format="float", example=1.50),
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
        $modifiers = \App\Modules\Catalog\Modifier\Models\ModifierGroup::with('options')->paginate(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Modifiers retrieved successfully.',
            'data' => $modifiers
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/modifiers",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Modifier",
     *     description="Create a new Modifier. Requires `admin.catalog` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", example="Milk Options"),
     *             @OA\Property(property="min_select", type="integer", example=0),
     *             @OA\Property(property="max_select", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Oat Milk"),
     *                     @OA\Property(property="price_delta", type="number", format="float", example=1.50)
     *                 )
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'name' => 'required|string|max:50',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:100',
            'options.*.price_delta' => 'required_with:options|numeric'
        ]);

        $group = \App\Modules\Catalog\Modifier\Models\ModifierGroup::create(\Illuminate\Support\Arr::except($validated, ['options']));

        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $optionData) {
                $group->options()->create($optionData);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => $group->load('options')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/modifiers/{id}",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Modifier",
     *     description="Retrieve a single Modifier by ID. Requires `admin.catalog` permission.",
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
     *                 @OA\Property(property="name", type="string", example="Extra Shot"),
     *                 @OA\Property(property="price", type="number", format="float", example=1.50),
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
        $modifier = \App\Modules\Catalog\Modifier\Models\ModifierGroup::with('options')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => $modifier
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/modifiers/{id}",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Modifier",
     *     description="Update an existing Modifier. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Milk Options"),
     *             @OA\Property(property="min_select", type="integer", example=0),
     *             @OA\Property(property="max_select", type="integer", example=1),
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
        $group = \App\Modules\Catalog\Modifier\Models\ModifierGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:50',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'is_active' => 'boolean'
        ]);

        $group->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => $group->load('options')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/modifiers/{id}",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Modifier",
     *     description="Soft-delete a Modifier. Requires `admin.catalog` permission.",
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
        $group = \App\Modules\Catalog\Modifier\Models\ModifierGroup::findOrFail($id);
        $group->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/{id}/modifiers",
     *     tags={"Modifiers Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Sync Modifiers",
     *     description="Sync modifiers to an item. Requires `admin.catalog` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="modifier_ids", type="array", @OA\Items(type="string", example="uuid"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Synced successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Modifiers synced successfully.")
     *         )
     *     )
     * )
     */
    public function syncModifiers(Request $request, $id)
    {
        $product = \App\Modules\Catalog\Product\Models\Product::findOrFail($id);
        
        $validated = $request->validate([
            'modifier_ids' => 'required|array',
            'modifier_ids.*' => 'uuid|exists:modifier_groups,id'
        ]);

        $product->modifiers()->sync($validated['modifier_ids']);

        return response()->json([
            'status' => 'success',
            'message' => 'Modifiers synced successfully.'
        ]);
    }
}
