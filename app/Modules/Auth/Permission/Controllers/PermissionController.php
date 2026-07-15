<?php

namespace App\Modules\Auth\Permission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Get all permissions grouped by category.
     */
    public function index()
    {
        $permissions = Permission::all()->groupBy('group_name');

        return response()->json([
            'status' => 'success',
            'data' => $permissions
        ]);
    }
}
