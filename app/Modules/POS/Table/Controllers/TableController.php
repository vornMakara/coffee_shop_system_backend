<?php

namespace App\Modules\POS\Table\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Table\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/tables",
     *     tags={"POS Core Data"},
     *     summary="List Tables",
     *     description="Returns all tables and their statuses.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="category", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Tables retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="table_number", type="string", example="T1"),
     *                     @OA\Property(property="status", type="string", example="available"),
     *                     @OA\Property(property="category", type="string", example="Indoor")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Table::query();
        if ($request->has('table_category_id')) {
            $query->where('table_category_id', $request->table_category_id);
        }
        $tables = $query->orderBy('number', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Tables retrieved successfully.',
            'data' => $tables
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tables/categories",
     *     tags={"POS Core Data"},
     *     summary="List Table Categories",
     *     description="Returns table zones (e.g., Indoor, Outdoor).",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Table categories retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="name", type="string", example="Indoor")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categories(Request $request)
    {
        $categories = \App\Modules\POS\Table\Models\TableCategory::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Table categories retrieved successfully.',
            'data' => $categories
        ]);
    }
}
