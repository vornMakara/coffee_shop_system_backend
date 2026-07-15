<?php

namespace App\Modules\Catalog\Modifier\Services;

use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Catalog\Modifier\Models\ModifierOption;
use Illuminate\Support\Facades\DB;
use Exception;

class ModifierService
{
    public function getAllModifiers()
    {
        return ModifierGroup::with('options')->get();
    }

    public function createModifier(array $data)
    {
        DB::beginTransaction();
        try {
            $group = ModifierGroup::create([
                'name' => $data['name'],
                'is_required' => $data['is_required'] ?? false,
                'min_selections' => $data['min_selections'] ?? 0,
                'max_selections' => $data['max_selections'] ?? 1,
            ]);

            foreach ($data['options'] as $opt) {
                ModifierOption::create([
                    'modifier_group_id' => $group->id,
                    'name' => $opt['name'],
                    'price_adjustment' => $opt['price_adjustment'] ?? 0
                ]);
            }

            DB::commit();

            return $group->load('options');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateModifier(ModifierGroup $group, array $data)
    {
        $group->update($data);
        return $group->load('options');
    }

    public function deleteModifier(ModifierGroup $group)
    {
        return $group->delete();
    }
}
