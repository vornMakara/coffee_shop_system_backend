<?php

namespace App\Modules\POS\Table\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\POS\Table\Models\Table;
use App\Modules\POS\Table\Requests\Admin\StoreTableRequest;
use App\Modules\POS\Table\Requests\Admin\UpdateTableRequest;
use App\Modules\POS\Table\Resources\Admin\TableResource;
use App\Modules\POS\Table\Services\Admin\TableService;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/tables",
     *     tags={"Tables Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="List Tables",
     *     description="Retrieve a paginated list of tables. Requires `admin.pos_setup` permission.",
     *     @OA\Response(
     *         response=200,
     *         description="Tables retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tables retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="branch_id", type="string", example="uuid"),
     *                         @OA\Property(property="table_number", type="string", example="T-01"),
     *                         @OA\Property(property="seating_capacity", type="integer", example=4),
     *                         @OA\Property(property="status", type="string", example="available"),
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
        $tables = $this->tableService->getPaginated(15);
        return response()->json([
            'status' => 'success',
            'message' => 'Tables retrieved successfully.',
            'data' => TableResource::collection($tables)->response()->getData(true)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/tables",
     *     tags={"Tables Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create Table",
     *     description="Create a new Table. Requires `admin.pos_setup` permission.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="branch_id", type="string", example="uuid"),
     *             @OA\Property(property="table_number", type="string", example="T-01"),
     *             @OA\Property(property="seating_capacity", type="integer", example=4),
     *             @OA\Property(property="status", type="string", example="available")
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
     *                 @OA\Property(property="branch_id", type="string", example="uuid"),
     *                 @OA\Property(property="table_number", type="string", example="T-01"),
     *                 @OA\Property(property="seating_capacity", type="integer", example=4),
     *                 @OA\Property(property="status", type="string", example="available"),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreTableRequest $request)
    {
        $validated = $request->validated();
        $data = [
            'branch_id' => $validated['branch_id'],
            'number' => $validated['table_number'],
            'capacity' => $validated['seating_capacity'] ?? 2,
            'status' => $validated['status'] ?? 'available'
        ];
        $table = $this->tableService->createTable($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully.',
            'data' => new TableResource($table)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/tables/{id}",
     *     tags={"Tables Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get Table",
     *     description="Retrieve a single Table by ID. Requires `admin.pos_setup` permission.",
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
     *                 @OA\Property(property="branch_id", type="string", example="uuid"),
     *                 @OA\Property(property="table_number", type="string", example="T-01"),
     *                 @OA\Property(property="seating_capacity", type="integer", example=4),
     *                 @OA\Property(property="status", type="string", example="available"),
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
        $table = Table::with('branch')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Details retrieved successfully.',
            'data' => new TableResource($table)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/tables/{id}",
     *     tags={"Tables Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update Table",
     *     description="Update an existing Table. Requires `admin.pos_setup` permission.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="table_number", type="string", example="T-01"),
     *             @OA\Property(property="seating_capacity", type="integer", example=4),
     *             @OA\Property(property="status", type="string", example="available")
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
     *                 @OA\Property(property="branch_id", type="string", example="uuid"),
     *                 @OA\Property(property="table_number", type="string", example="T-01"),
     *                 @OA\Property(property="seating_capacity", type="integer", example=4),
     *                 @OA\Property(property="status", type="string", example="available"),
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="created_at", type="string", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateTableRequest $request, $id)
    {
        $table = Table::findOrFail($id);
        
        $validated = $request->validated();
        $data = [];
        if (isset($validated['table_number'])) $data['number'] = $validated['table_number'];
        if (isset($validated['seating_capacity'])) $data['capacity'] = $validated['seating_capacity'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];

        $table = $this->tableService->updateTable($table, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully.',
            'data' => new TableResource($table)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/tables/{id}",
     *     tags={"Tables Management"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete Table",
     *     description="Soft-delete a Table. Requires `admin.pos_setup` permission.",
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
        $table = Table::findOrFail($id);
        $this->tableService->deleteTable($table);

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully.'
        ]);
    }
}
