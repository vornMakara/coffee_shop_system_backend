<?php

namespace App\Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\ModifierGroup;
use App\Modules\Catalog\Models\ModifierOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModifierController extends Controller
{
    public function index()
    {
        $groups = ModifierGroup::with('options')->get();
        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_required' => 'boolean',
            'min_selections' => 'integer|min:0',
            'max_selections' => 'integer|min:1',
            'options' => 'required|array',
            'options.*.name' => 'required|string|max:255',
            'options.*.price_adjustment' => 'numeric|default:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $group = ModifierGroup::create($request->only(['name', 'is_required', 'min_selections', 'max_selections']));

            foreach ($request->options as $opt) {
                ModifierOption::create([
                    'modifier_group_id' => $group->id,
                    'name' => $opt['name'],
                    'price_adjustment' => $opt['price_adjustment'] ?? 0
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Modifier group created successfully.',
                'data' => $group->load('options')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to create modifier group: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $group = ModifierGroup::find($id);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'Modifier group not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'is_required' => 'boolean',
            'min_selections' => 'integer|min:0',
            'max_selections' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $group->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Modifier group updated successfully.',
            'data' => $group->load('options')
        ]);
    }

    public function destroy($id)
    {
        $group = ModifierGroup::find($id);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'Modifier group not found'], 404);
        }

        $group->delete(); // Soft delete cascades (if setup) or leaves options orphaned/soft-deleted.

        return response()->json([
            'status' => 'success',
            'message' => 'Modifier group deleted successfully.'
        ]);
    }
}
