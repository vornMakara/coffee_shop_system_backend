<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return response()->json([
            'status' => 'success',
            'data' => $branches
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:branches,name',
            'location' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $branch = Branch::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully.',
            'data' => $branch
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:branches,name,' . $id,
            'location' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $branch->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Branch updated successfully.',
            'data' => $branch
        ]);
    }

    public function destroy($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        $branch->delete(); // Soft delete

        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully.'
        ]);
    }
}
