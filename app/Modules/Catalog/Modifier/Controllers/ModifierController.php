<?php

namespace App\Modules\Catalog\Modifier\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Catalog\Modifier\Requests\StoreModifierRequest;
use App\Modules\Catalog\Modifier\Requests\UpdateModifierRequest;
use App\Modules\Catalog\Modifier\Resources\ModifierResource;
use App\Modules\Catalog\Modifier\Services\ModifierService;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    protected $modifierService;

    public function __construct(ModifierService $modifierService)
    {
        $this->modifierService = $modifierService;
    }
    public function index()
    {
        $groups = $this->modifierService->getAllModifiers();
        return response()->json([
            'status' => 'success',
            'data' => ModifierResource::collection($groups)
        ]);
    }

    public function store(StoreModifierRequest $request)
    {
        try {
            $group = $this->modifierService->createModifier($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Modifier group created successfully.',
                'data' => new ModifierResource($group)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to create modifier group: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateModifierRequest $request, $id)
    {
        $group = ModifierGroup::find($id);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'Modifier group not found'], 404);
        }

        $group = $this->modifierService->updateModifier($group, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Modifier group updated successfully.',
            'data' => new ModifierResource($group)
        ]);
    }

    public function destroy($id)
    {
        $group = ModifierGroup::find($id);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'Modifier group not found'], 404);
        }

        $this->modifierService->deleteModifier($group);

        return response()->json([
            'status' => 'success',
            'message' => 'Modifier group deleted successfully.'
        ]);
    }
}
