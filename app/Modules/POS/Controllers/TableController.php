<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Get a list of all tables.
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
     * Get a list of unique table categories.
     */
    public function categories(Request $request)
    {
        $categories = \App\Modules\POS\Models\TableCategory::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Table categories retrieved successfully.',
            'data' => $categories
        ]);
    }
}
